<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\Call;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Project;
use App\Models\User;
use App\Services\BrokerLeadService;
use App\Services\DuplicateLeadService;
use App\Services\LeadAssignmentService;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isBroker()) {
            return redirect()->route('dashboard');
        }

        $query = Lead::with(['assignedTo', 'broker', 'brokerLead', 'project', 'source', 'assignments.assignedTo', 'calls.user']);

        // Sales Executive Privacy Isolation: Sales Executives see ONLY their assigned leads
        if ($user->isSales()) {
            $query->where('assigned_to_user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('lead_code', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->paginate(10);

        /*
        // AI Engine Metrics (Temporarily commented out)
        $aiService = app(\App\Services\AiIntelligenceService::class);
        foreach ($leads as $l) {
            $l->ai_score_data = $aiService->calculateLeadScore($l);
            $l->ai_recommendations_data = $aiService->getSmartPropertyRecommendations($l);
            $l->ai_coaching_data = $aiService->generateSalesExecutiveCoaching($l);
            $latestCall = $l->calls->first();
            $l->ai_call_analysis_data = $aiService->generateCallSummaryAndSentiment($latestCall?->notes, $latestCall?->call_outcome);
        }
        */

        $sources = LeadSource::all();
        $projects = Project::all();

        // Fetch ONLY Sales Executives for the lead assignment dropdown
        $salesExecutives = User::where('company_id', $user->company_id)
            ->whereHas('role', function ($q) {
                $q->where('slug', 'sales_executive');
            })
            ->get();

        $brokers = Broker::all();

        return view('leads.index', compact('leads', 'sources', 'projects', 'salesExecutives', 'brokers'));
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        if ($user->isBroker()) {
            return redirect()->route('dashboard');
        }

        $query = Lead::with(['assignedTo', 'broker', 'project', 'source']);

        if ($user->isSales()) {
            $query->where('assigned_to_user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('lead_code', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->get();

        $filename = "REOS_CRM_Leads_Export_" . now()->format('Y-m-d_His') . ".csv";

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($leads) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Lead Code',
                'Customer Name',
                'Phone',
                'Email',
                'Status',
                'Assigned Sales Executive',
                'Interested Project',
                'Budget Min (₹)',
                'Budget Max (₹)',
                'Source',
                'Created At'
            ]);

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->lead_code,
                    trim($lead->first_name . ' ' . $lead->last_name),
                    $lead->phone,
                    $lead->email ?? 'N/A',
                    strtoupper(str_replace('_', ' ', $lead->status)),
                    $lead->assignedTo->name ?? 'Unassigned',
                    $lead->project->name ?? 'N/A',
                    $lead->budget_min ? number_format($lead->budget_min) : 'N/A',
                    $lead->budget_max ? number_format($lead->budget_max) : 'N/A',
                    $lead->broker ? 'Broker Channel: ' . ($lead->broker->agency_name ?? 'Broker') : ($lead->source->name ?? 'Direct'),
                    $lead->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        \App\Services\AuditLogService::log('leads_exported', "Exported " . $leads->count() . " CRM leads to Excel/CSV.", null, null, ['count' => $leads->count()]);

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request, DuplicateLeadService $duplicateService)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'source_id' => 'nullable|exists:lead_sources,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'interested_project_id' => 'required|exists:projects,id',
            'interested_unit_type' => 'nullable|string',
            'budget_min' => 'nullable|numeric',
            'budget_max' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $project = Project::withoutGlobalScopes()->findOrFail($validated['interested_project_id']);

        $duplicate = $duplicateService->findDuplicate($project->company_id, $validated['phone'], $validated['email'] ?? null);

        $leadCode = 'LD-' . rand(1000, 9999);

        $lead = Lead::create([
            'company_id' => $project->company_id,
            'lead_code' => $leadCode,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? '',
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'alternate_phone' => $validated['alternate_phone'] ?? null,
            'source_id' => $validated['source_id'] ?? null,
            'broker_id' => $validated['broker_id'] ?? null,
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? ($user->isSales() ? $user->id : null),
            'interested_project_id' => $project->id,
            'interested_unit_type' => $validated['interested_unit_type'] ?? null,
            'budget_min' => $validated['budget_min'] ?? null,
            'budget_max' => $validated['budget_max'] ?? null,
            'status' => 'new',
            'is_duplicate' => $duplicate ? true : false,
            'duplicate_of_lead_id' => $duplicate?->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        // If a broker_id is set, link BrokerLead pivot record
        if ($lead->broker_id) {
            BrokerLead::withoutGlobalScopes()->firstOrCreate(
                ['lead_id' => $lead->id],
                [
                    'company_id' => $lead->company_id,
                    'broker_id' => $lead->broker_id,
                    'project_id' => $lead->interested_project_id,
                    'submitted_at' => now(),
                    'broker_visible_status' => 'Submitted',
                ]
            );
        }

        \App\Services\AuditLogService::log('lead_created', "Created CRM Lead {$lead->lead_code} for {$lead->first_name} {$lead->last_name}.", $lead, null, ['lead_code' => $lead->lead_code, 'phone' => $lead->phone, 'status' => $lead->status]);

        // Email Notification to Assigned Executive
        if ($lead->assignedTo) {
            app(\App\Services\NotificationService::class)->notify(
                $lead->assignedTo,
                'lead_assigned',
                "🎯 New CRM Lead Assigned: {$lead->first_name} {$lead->last_name}",
                "Hello {$lead->assignedTo->name}, a new lead '{$lead->first_name} {$lead->last_name}' ({$lead->phone}) has been registered and assigned to you for project '{$project->name}'.",
                url("/leads/{$lead->id}")
            );
        }

        if ($duplicate) {
            return redirect()->route('leads.index')->with('warning', "Lead created! DUPLICATE DETECTED for phone/email within company.");
        }

        return redirect()->route('leads.index')->with('success', "Lead {$lead->lead_code} created successfully!");
    }

    public function updateStatus(Request $request, Lead $lead, LeadService $leadService)
    {
        $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        if ($request->status === 'converted') {
            return back()->with('error', 'Leads cannot be manually marked as Converted from the status dropdown. Please create a Unit Booking under Bookings & Units to convert this lead.');
        }

        try {
            $oldStatus = $lead->status;
            $leadService->updateStatus($lead, $request->status, $request->notes, Auth::user());
            \App\Services\AuditLogService::log('lead_status_updated', "Updated Lead {$lead->lead_code} status to {$request->status}.", $lead, ['old_status' => $oldStatus], ['new_status' => $request->status]);

            // Email Notification to Sales Managers / Admins for status update
            $managers = User::where('company_id', Auth::user()->company_id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('slug', ['sales_manager', 'company_admin', 'founder', 'director']);
                })
                ->where('id', '!=', Auth::id())
                ->get();

            $execName = Auth::user()->name;
            $customerName = trim($lead->first_name . ' ' . $lead->last_name);
            $formattedStatus = strtoupper(str_replace('_', ' ', $request->status));

            $isNegotiation = ($request->status === 'negotiation');
            $emailTitle = $isNegotiation 
                ? "🚨 URGENT: Lead Reached NEGOTIATION Stage - {$customerName}"
                : "📈 Lead Status Updated: {$customerName} → {$formattedStatus}";

            $emailMessage = $isNegotiation
                ? "Sales Executive {$execName} has advanced lead '{$customerName}' ({$lead->lead_code}) to the NEGOTIATION stage for project '{$lead->project?->name}'. Remarks: " . ($request->notes ?? 'None') . ". Please review pricing/discount terms immediately and assist executive to finalize booking!"
                : "Sales Executive {$execName} updated status of lead '{$customerName}' ({$lead->lead_code}) to {$formattedStatus}. Remarks: " . ($request->notes ?? 'None') . ". Review details on REOS to direct next steps.";

            foreach ($managers as $manager) {
                app(\App\Services\NotificationService::class)->notify(
                    $manager,
                    $isNegotiation ? 'lead_negotiation_stage' : 'lead_status_changed',
                    $emailTitle,
                    $emailMessage,
                    url("/leads/{$lead->id}")
                );
            }

            return back()->with('success', "Lead status updated to {$request->status}. Broker view automatically synced!");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function assign(Request $request, Lead $lead, LeadAssignmentService $assignmentService)
    {
        Gate::authorize('assign-leads');

        $request->validate([
            'assigned_to_user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string',
        ]);

        $salesUser = User::findOrFail($request->assigned_to_user_id);
        $assignmentService->assignLead($lead, $salesUser, Auth::user(), $request->reason);

        \App\Services\AuditLogService::log('lead_assigned', "Assigned Lead {$lead->lead_code} to Sales Executive {$salesUser->name}.", $lead, null, ['assigned_to' => $salesUser->name]);

        // Dispatch Email Notification to Sales Executive
        app(\App\Services\NotificationService::class)->notify(
            $salesUser,
            'lead_assigned',
            "🎯 Lead Assigned: {$lead->first_name} {$lead->last_name} ({$lead->lead_code})",
            "Hello {$salesUser->name}, CRM Lead '{$lead->first_name} {$lead->last_name}' ({$lead->phone}) has been assigned to you. Please log your call or site visit on REOS.",
            url("/leads/{$lead->id}")
        );

        return back()->with('success', "Lead {$lead->lead_code} assigned to {$salesUser->name} with full history preserved.");
    }

    public function logCall(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'call_outcome' => 'required|string',
            'notes' => 'nullable|string',
            'next_followup_at' => 'nullable|date',
        ]);

        $outcomeText = ucwords(str_replace('_', ' ', $validated['call_outcome']));

        $dbOutcome = match ($validated['call_outcome']) {
            'site_visit_conducted', 'interested_after_visit', 'spoke_interested', 'connected' => 'connected',
            'scheduled_site_visit', 'busy_callback', 'callback_required' => 'callback_required',
            'no_answer', 'not_connected' => 'not_connected',
            'busy' => 'busy',
            default => 'connected',
        };

        $notesContent = $validated['notes'] ? "[{$outcomeText}] " . $validated['notes'] : "[{$outcomeText}] Outcome logged.";

        Call::create([
            'company_id' => Auth::user()->company_id,
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'call_outcome' => $dbOutcome,
            'notes' => $notesContent,
            'called_at' => now(),
            'next_followup_at' => $validated['next_followup_at'] ?? null,
        ]);

        if (!empty($validated['next_followup_at'])) {
            FollowUp::create([
                'company_id' => Auth::user()->company_id,
                'lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'scheduled_at' => $validated['next_followup_at'],
                'status' => 'pending',
                'notes' => "Follow-up scheduled from call/visit outcome: {$outcomeText}",
            ]);
        }

        // Email Notification to Sales Managers & Admins when Sales Executive logs work
        $managers = User::where('company_id', Auth::user()->company_id)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['sales_manager', 'company_admin', 'founder', 'director']);
            })
            ->where('id', '!=', Auth::id())
            ->get();

        $execName = Auth::user()->name;
        $customerName = trim($lead->first_name . ' ' . $lead->last_name);

        foreach ($managers as $manager) {
            app(\App\Services\NotificationService::class)->notify(
                $manager,
                'sales_activity_logged',
                "📞 Activity Update from {$execName} on {$customerName}",
                "Sales Executive {$execName} logged work on lead '{$customerName}' ({$lead->lead_code}). Outcome: {$outcomeText}. Remarks: " . ($validated['notes'] ?? 'None') . ". Please review on REOS to direct next steps.",
                url("/leads/{$lead->id}")
            );
        }

        return back()->with('success', 'Call / Visit outcome logged and follow-up scheduled successfully!');
    }

    public function show($id, \App\Services\AiIntelligenceService $aiService)
    {
        $lead = Lead::with(['assignedTo', 'broker', 'brokerLead', 'project', 'source', 'activities.user', 'calls.user', 'followUps', 'siteVisits.assignedTo'])->findOrFail($id);

        $aiScore = $aiService->calculateLeadScore($lead);
        $recommendations = $aiService->getSmartPropertyRecommendations($lead);
        $coaching = $aiService->generateSalesExecutiveCoaching($lead);

        $latestCall = $lead->calls->first();
        $callAnalysis = $aiService->generateCallSummaryAndSentiment($latestCall?->notes, $latestCall?->call_outcome);

        return view('leads.show', compact('lead', 'aiScore', 'recommendations', 'coaching', 'callAnalysis'));
    }

    public function destroy(Lead $lead)
    {
        if (!Auth::user()->isCompanyAdmin() && !Auth::user()->isManager() && Auth::user()->role?->slug !== 'founder') {
            return back()->with('error', 'Only Admins and Managers can delete leads.');
        }

        $leadCode = $lead->lead_code;
        $customerName = "{$lead->first_name} {$lead->last_name}";
        $lead->delete();

        \App\Services\AuditLogService::log('lead_deleted', "Deleted Lead {$leadCode} ({$customerName}).", null);

        return redirect()->route('leads.index')->with('success', "Lead {$leadCode} ({$customerName}) deleted successfully.");
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'interested_unit_type' => 'nullable|string',
            'budget_max' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $lead->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? '',
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'interested_unit_type' => $validated['interested_unit_type'] ?? $lead->interested_unit_type,
            'budget_max' => $validated['budget_max'] ?? $lead->budget_max,
            'notes' => $validated['notes'] ?? $lead->notes,
        ]);

        \App\Services\AuditLogService::log('lead_updated', "Updated Lead {$lead->lead_code} details.", null);

        return back()->with('success', "Lead {$lead->lead_code} details updated successfully!");
    }
}
