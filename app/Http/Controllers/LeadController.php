<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\Call;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Project;
use App\Models\SaasApprovalRequest;
use App\Models\User;
use App\Services\BrokerLeadService;
use App\Services\DuplicateLeadService;
use App\Services\LeadAssignmentService;
use App\Services\LeadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        Gate::authorize('manage-leads');

        $query = Lead::with(['assignedTo', 'assignedManager', 'broker', 'brokerLead', 'project', 'source', 'assignments.assignedTo', 'calls.user', 'latestDistributionLog.rule']);

        // Privacy Isolation: Executives see assigned leads; Managers see leads in their manager pool
        if ($user->isSales()) {
            $query->where('assigned_to_user_id', $user->id);
        } elseif ($user->isManager()) {
            $teamExecutiveIds = User::where('company_id', $user->company_id)
                ->where('reporting_manager_id', $user->id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('slug', ['sales_executive', 'executive']);
                })
                ->pluck('id');

            $query->where(function ($q) use ($user, $teamExecutiveIds) {
                $q->where('assigned_to_manager_id', $user->id)
                    ->orWhereIn('assigned_to_user_id', $teamExecutiveIds);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Employee / Executive Filter
        if ($request->filled('assigned_to_user_id') || $request->filled('employee_id')) {
            $empId = $request->get('assigned_to_user_id') ?: $request->get('employee_id');
            if ($empId === 'unassigned') {
                $query->whereNull('assigned_to_user_id');
            } else {
                $query->where('assigned_to_user_id', $empId);
            }
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

        $leads = $query->latest()->paginate(10)->appends($request->all());

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

        // Fetch Sales Executives & Employees list for filters and assignments
        $employeesQuery = User::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->with('role')
            ->orderBy('name');

        if ($user->isManager()) {
            $employeesQuery->where('reporting_manager_id', $user->id);
        }

        $employees = $employeesQuery->get();

        $salesExecutives = $employees->filter(function ($u) {
            return $u->role?->slug === 'sales_executive' || $u->isSales();
        });

        $brokers = Broker::all();

        return view('leads.index', compact('leads', 'sources', 'projects', 'employees', 'salesExecutives', 'brokers'));
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        Gate::authorize('export-leads');

        $query = Lead::with(['assignedTo', 'broker', 'project', 'source']);

        if ($user->isSales()) {
            $query->where('assigned_to_user_id', $user->id);
        } elseif ($user->isManager()) {
            $teamExecutiveIds = User::where('company_id', $user->company_id)
                ->where('reporting_manager_id', $user->id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('slug', ['sales_executive', 'executive']);
                })
                ->pluck('id');

            $query->where(function ($q) use ($user, $teamExecutiveIds) {
                $q->where('assigned_to_manager_id', $user->id)
                    ->orWhereIn('assigned_to_user_id', $teamExecutiveIds);
            });
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
        Gate::authorize('manage-leads');

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

        // Two-Tier Lead Auto Distribution (Manager Round-Robin Pool)
        app(\App\Services\LeadDistributionService::class)->distributeNewLead($lead, app(\App\Services\NotificationService::class));

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
                    $q->whereIn('slug', ['admin', 'company_admin', 'manager', 'sales_manager', 'founder', 'director']);
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

    /**
     * Manually transfer a lead to a different executive.
     * Available to: Manager, Admin, Director.
     * Reason is mandatory. Full history is preserved in lead_assignments.
     */
    public function transfer(Request $request, Lead $lead, LeadAssignmentService $assignmentService)
    {
        Gate::authorize('assign-leads');

        $request->validate([
            'new_assignee_id'   => 'required|exists:users,id|different:lead.assigned_to_user_id',
            'transfer_reason'   => 'required|string|max:100',
            'transfer_note'     => 'nullable|string|max:500',
        ]);

        $newAssignee     = User::findOrFail($request->new_assignee_id);
        $previousAssignee = $lead->assignedTo;
        $user             = Auth::user();

        // Max transfer guard — prevent infinite bouncing
        if ($lead->transfer_count >= 5) {
            return back()->with('error', "Lead {$lead->lead_code} has already been transferred {$lead->transfer_count} times. Please escalate manually to your manager.");
        }

        $assignmentService->transferLead(
            $lead,
            $newAssignee,
            $user,
            $request->transfer_reason,
            $request->transfer_note,
            'manual'
        );

        \App\Services\AuditLogService::log(
            'lead_transferred',
            "Manually transferred Lead {$lead->lead_code} from " . ($previousAssignee->name ?? 'Unassigned') . " to {$newAssignee->name}. Reason: {$request->transfer_reason}",
            $lead,
            ['from' => $previousAssignee?->name],
            ['to' => $newAssignee->name, 'reason' => $request->transfer_reason]
        );

        // Notify NEW executive
        app(\App\Services\NotificationService::class)->notify(
            $newAssignee,
            'lead_transferred',
            "🔄 Lead Transferred to You: {$lead->first_name} {$lead->last_name} ({$lead->lead_code})",
            "Hello {$newAssignee->name}, Lead '{$lead->first_name} {$lead->last_name}' ({$lead->phone}) has been transferred to you by {$user->name}. Reason: {$request->transfer_reason}. Please follow up immediately on REOS.",
            url("/leads/{$lead->id}")
        );

        // Notify PREVIOUS executive (if any)
        if ($previousAssignee && $previousAssignee->id !== $user->id) {
            app(\App\Services\NotificationService::class)->notify(
                $previousAssignee,
                'lead_removed',
                "📋 Lead Reassigned Away: {$lead->first_name} {$lead->last_name} ({$lead->lead_code})",
                "Hello {$previousAssignee->name}, Lead '{$lead->first_name} {$lead->last_name}' has been transferred to {$newAssignee->name} by {$user->name}. Reason: {$request->transfer_reason}.",
                url("/leads/{$lead->id}")
            );
        }

        return back()->with('success', "Lead {$lead->lead_code} successfully transferred to {$newAssignee->name}. Transfer #{$lead->transfer_count} recorded.");
    }

    public function logCall(Request $request, Lead $lead, LeadAssignmentService $assignmentService)
    {
        $validated = $request->validate([
            'call_outcome'      => 'required|string',
            'notes'            => 'nullable|string',
            'next_followup_at'  => 'nullable|date',
            'status'            => 'nullable|string',
            'budget_max'        => 'nullable|numeric',
            'sv_scheduled_at'   => 'nullable|date|required_if:status,READY TO VISIT',
            'sv_project_id'     => 'nullable|exists:projects,id|required_if:status,READY TO VISIT',
            'sv_assigned_to'    => 'nullable|exists:users,id',
            'sv_broker_name'    => 'nullable|string',
            'sv_broker_phone'   => 'nullable|string',
            'sv_broker_company' => 'nullable|string',
            'sv_visit_description' => 'nullable|string',
            'sv_remark_1'       => 'nullable|string',
            'sv_remark_2'       => 'nullable|string',
            'audio_recording'   => 'nullable|file|mimes:mp3,wav,ogg,m4a,webm,aac,flac|max:51200', // max 50MB
        ]);

        $outcomeText = ucwords(str_replace('_', ' ', $validated['call_outcome']));

        $dbOutcome = match ($validated['call_outcome']) {
            'site_visit_conducted', 'interested_after_visit', 'spoke_interested', 'connected' => 'connected',
            'scheduled_site_visit', 'busy_callback', 'callback_required' => 'callback_required',
            'no_answer', 'not_connected' => 'not_connected',
            'busy' => 'busy',
            default => 'connected',
        };

        // Append Negotiation Details to Notes if Warm / Negotiation is selected
        $finalNotes = $validated['notes'] ?? '';
        if (isset($validated['status']) && ($validated['status'] === 'Warm / Negotiation' || $validated['status'] === 'negotiation' || $validated['status'] === 'WARM')) {
            $negPrice = $request->input('neg_offered_price');
            $negDate = $request->input('neg_expected_close_date');
            $negRemarks = $request->input('neg_remarks');
            
            $negDetails = [];
            if ($negPrice) $negDetails[] = "Offered Price: ₹" . number_format($negPrice);
            if ($negDate) $negDetails[] = "Expected Closing: " . date('d M Y', strtotime($negDate));
            if ($negRemarks) $negDetails[] = "Remarks: " . $negRemarks;
            
            if (!empty($negDetails)) {
                $finalNotes .= ($finalNotes ? "\n\n" : "") . "--- NEGOTIATION DETAILS ---\n" . implode("\n", $negDetails);
            }
        }
        
        // Append Converted/Lost details
        if (isset($validated['status']) && $validated['status'] === 'Token Received') {
            $bookAmt = $request->input('booking_amount');
            $bookUnit = $request->input('booking_unit');
            $finalNotes .= ($finalNotes ? "\n\n" : "") . "--- BOOKING DETAILS ---\nAmount: ₹" . number_format($bookAmt) . "\nUnit: " . ($bookUnit ?: 'Not specified');
        } elseif (isset($validated['status']) && $validated['status'] === 'Not Interested') {
            $lostReason = $request->input('lost_reason');
            $finalNotes .= ($finalNotes ? "\n\n" : "") . "--- LOST REASON ---\n" . ($lostReason ?: 'No reason provided');
        }

        $notesContent = $finalNotes ? "[{$outcomeText}] " . $finalNotes : "[{$outcomeText}] Outcome logged.";

        // Handle audio recording upload
        $audioPath = null;
        $audioName = null;
        if ($request->hasFile('audio_recording') && $request->file('audio_recording')->isValid()) {
            $file      = $request->file('audio_recording');
            $audioName = $file->getClientOriginalName();
            $filename  = 'call_' . $lead->id . '_' . Auth::id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $audioPath = $file->storeAs('call-recordings', $filename, 'public');
        }

        Call::create([
            'company_id'           => Auth::user()->company_id,
            'lead_id'              => $lead->id,
            'user_id'              => Auth::id(),
            'call_outcome'         => $dbOutcome,
            'notes'                => $notesContent,
            'audio_recording_path' => $audioPath,
            'audio_recording_name' => $audioName,
            'called_at'            => now(),
            'next_followup_at'     => $validated['next_followup_at'] ?? null,
        ]);

        // Update Budget if provided
        if (isset($validated['budget_max'])) {
            $lead->budget_max = $validated['budget_max'];
            $lead->save();
        }

        // Update last_activity_at — resets auto-transfer eligibility
        $assignmentService->updateLastActivity($lead);

        if (!empty($validated['next_followup_at']) && (!isset($validated['status']) || ($validated['status'] !== 'Ready for Site Visit' && $validated['status'] !== 'Token Received' && $validated['status'] !== 'Not Interested'))) {
            FollowUp::create([
                'company_id'   => Auth::user()->company_id,
                'lead_id'      => $lead->id,
                'user_id'      => Auth::id(),
                'scheduled_at' => $validated['next_followup_at'],
                'status'       => 'pending',
                'notes'        => "Follow-up scheduled from call/visit outcome: {$outcomeText}",
            ]);
        }

        // Handle Pipeline Stage Update & Site Visit Scheduling
        if (isset($validated['status'])) {
            if ($validated['status'] === 'Ready for Site Visit' || $validated['status'] === 'READY TO VISIT' || $validated['status'] === 'site_visit_scheduled') {
                $lead->status = 'site_visit';
                
                \App\Models\SiteVisit::create([
                    'company_id' => Auth::user()->company_id,
                    'lead_id' => $lead->id,
                    'project_id' => $validated['sv_project_id'],
                    'assigned_to_user_id' => $validated['sv_assigned_to'] ?? Auth::id(),
                    'scheduled_at' => $validated['sv_scheduled_at'],
                    'status' => 'scheduled',
                    'notes' => 'Site visit scheduled from follow-up call. ' . $validated['notes'],
                    'broker_name' => $validated['sv_broker_name'] ?? null,
                    'broker_phone' => $validated['sv_broker_phone'] ?? null,
                    'broker_company' => $validated['sv_broker_company'] ?? null,
                    'visit_description' => $validated['sv_visit_description'] ?? null,
                    'remark_1' => $validated['sv_remark_1'] ?? null,
                    'remark_2' => $validated['sv_remark_2'] ?? null,
                ]);
                
                // Notify the assigned executive if it's someone else
                if (isset($validated['sv_assigned_to']) && $validated['sv_assigned_to'] != Auth::id()) {
                    $svExecutive = User::find($validated['sv_assigned_to']);
                    if ($svExecutive) {
                        app(\App\Services\NotificationService::class)->notify(
                            $svExecutive,
                            'site_visit_assigned',
                            "📅 Site Visit Assigned: {$lead->first_name}",
                            "You have been assigned a site visit for {$lead->first_name} on " . date('d M Y, h:i A', strtotime($validated['sv_scheduled_at'])) . ". Please check your schedule.",
                            url("/site-visits")
                        );
                    }
                }
            } elseif ($validated['status'] === 'Token Received') {
                $lead->status = 'converted';
            } elseif ($validated['status'] === 'Not Interested' || $validated['status'] === 'dropped') {
                $lead->status = 'lost';
                if ($request->has('lost_reason')) {
                    $lead->lost_reason = $request->input('lost_reason');
                }
            } elseif ($validated['status'] === 'Warm / Negotiation' || $validated['status'] === 'WARM' || $validated['status'] === 'negotiation') {
                $lead->status = 'negotiation';
            } elseif ($validated['status'] === 'In Follow-up' || $validated['status'] === 'IN FOLLOWUP' || $validated['status'] === 'Meeting at Client Place') {
                $lead->status = 'follow_up';
            } elseif ($validated['status'] === 'Not Connected' || $validated['status'] === 'NOT CONNECTED') {
                // If it's a new lead and we tried to call but didn't connect, move it to 'contacted'
                if ($lead->status === 'new') {
                    $lead->status = 'contacted';
                }
            }
            $lead->save();
        }

        // Email Notification to Sales Managers & Admins when Sales Executive logs work
        $managers = User::where('company_id', Auth::user()->company_id)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['admin', 'company_admin', 'manager', 'sales_manager', 'founder', 'director']);
            })
            ->where('id', '!=', Auth::id())
            ->get();

        $execName     = Auth::user()->name;
        $customerName = trim($lead->first_name . ' ' . $lead->last_name);
        $audioNote    = $audioPath ? " 🎙️ Audio recording attached." : "";

        foreach ($managers as $manager) {
            app(\App\Services\NotificationService::class)->notify(
                $manager,
                'sales_activity_logged',
                "📞 Activity Update from {$execName} on {$customerName}",
                "Sales Executive {$execName} logged work on lead '{$customerName}' ({$lead->lead_code}). Outcome: {$outcomeText}. Remarks: " . ($validated['notes'] ?? 'None') . ".{$audioNote} Please review on REOS to direct next steps.",
                url("/leads/{$lead->id}")
            );
        }

        $successMsg = 'Call / Visit outcome logged and follow-up scheduled successfully!';
        if ($audioPath) {
            $successMsg .= ' 🎙️ Audio recording uploaded.';
        }

        return back()->with('success', $successMsg);
    }

    public function createSiteVisit(Lead $lead)
    {
        $projects = \App\Models\Project::where('company_id', Auth::user()->company_id)->where('status', 'active')->get();
        $users = \App\Models\User::where('company_id', Auth::user()->company_id)->get();
        $brokers = \App\Models\Broker::where('company_id', Auth::user()->company_id)->where('status', 'active')->get();
        return view('leads.actions.site_visit', compact('lead', 'projects', 'users', 'brokers'));
    }

    public function createNegotiation(Lead $lead)
    {
        abort_if(Auth::user()->isSales(), 403, 'Sales Executives are not authorized to start negotiations.');
        return view('leads.actions.negotiation', compact('lead'));
    }

    public function createConvert(Lead $lead)
    {
        abort_if(Auth::user()->isSales(), 403, 'Sales Executives are not authorized to record bookings.');
        $projects = \App\Models\Project::where('company_id', Auth::user()->company_id)->where('status', 'active')->get();
        $brokers = \App\Models\Broker::where('company_id', Auth::user()->company_id)->where('status', 'active')->get();
        return view('leads.actions.convert', compact('lead', 'projects', 'brokers'));
    }

    public function createDrop(Lead $lead)
    {
        return view('leads.actions.drop', compact('lead'));
    }
    public function scheduleSiteVisit(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'sv_scheduled_at' => 'required|date',
            'sv_project_id' => 'required|exists:projects,id',
            'sv_assigned_to' => 'required|exists:users,id',
            'pickup_location' => 'nullable|string',
            'sv_broker_name' => 'nullable|string',
            'sv_broker_phone' => 'nullable|string',
            'sv_broker_company' => 'nullable|string',
            'sv_visit_description' => 'nullable|string',
            'remark_1' => 'nullable|string',
            'remark_2' => 'nullable|string',
            'inquiry_status' => 'required|string',
            'next_followup_date' => 'required|date',
            'followup_remark' => 'nullable|string',
        ]);

        $statusMap = [
            'IN FOLLOWUP' => 'in_followup',
            'UNDER NEGOTIATION' => 'negotiation',
            'SITE VISIT' => 'site_visit',
            'BOOKED' => 'converted'
        ];
        $lead->status = $statusMap[$validated['inquiry_status']] ?? 'site_visit';
        $lead->save();

        \App\Models\SiteVisit::create([
            'company_id' => Auth::user()->company_id,
            'lead_id' => $lead->id,
            'project_id' => $validated['sv_project_id'],
            'assigned_to_user_id' => $validated['sv_assigned_to'],
            'scheduled_at' => $validated['sv_scheduled_at'],
            'status' => 'scheduled',
            'pickup_location' => $validated['pickup_location'] ?? null,
            'notes' => 'Scheduled via Pipeline Action. ' . ($validated['sv_visit_description'] ?? ''),
            'broker_name' => $validated['sv_broker_name'] ?? null,
            'broker_phone' => $validated['sv_broker_phone'] ?? null,
            'broker_company' => $validated['sv_broker_company'] ?? null,
            'visit_description' => $validated['sv_visit_description'] ?? null,
            'remark_1' => $validated['remark_1'] ?? null,
            'remark_2' => $validated['remark_2'] ?? null,
        ]);

        \App\Models\LeadActivity::create([
            'company_id' => Auth::user()->company_id,
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'site_visit_scheduled',
            'description' => 'Site Visit logged/scheduled for ' . date('d M Y h:i A', strtotime($validated['sv_scheduled_at'])),
        ]);

        // Log the Followup Action
        \App\Models\LeadActivity::create([
            'company_id' => Auth::user()->company_id,
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'follow_up',
            'description' => ($validated['followup_remark'] ?? 'Site visit logged. Follow-up scheduled.') . 
                             ' (Next Follow-up: ' . date('d M Y h:i A', strtotime($validated['next_followup_date'])) . ')',
        ]);

        if ($validated['sv_assigned_to'] != Auth::id()) {
            $svExecutive = \App\Models\User::find($validated['sv_assigned_to']);
            if ($svExecutive) {
                app(\App\Services\NotificationService::class)->notify(
                    $svExecutive,
                    'site_visit_assigned',
                    "📅 Site Visit Assigned: {$lead->first_name}",
                    "You have been assigned a site visit for {$lead->first_name} on " . date('d M Y h:i A', strtotime($validated['sv_scheduled_at'])) . ".",
                    url("/site-visits")
                );
            }
        }

        return back()->with('success', 'Site Visit scheduled successfully.');
    }

    public function startNegotiation(Request $request, Lead $lead)
    {
        abort_if(Auth::user()->isSales(), 403, 'Sales Executives are not authorized to start negotiations.');
        $validated = $request->validate([
            'neg_offered_price' => 'required|numeric',
            'neg_expected_close_date' => 'required|date',
            'neg_remarks' => 'nullable|string',
        ]);

        $lead->status = 'negotiation';
        $lead->save();

        \App\Models\LeadActivity::create([
            'company_id' => Auth::user()->company_id,
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'negotiation_started',
            'description' => "Negotiation started. Offered Price: ₹" . number_format($validated['neg_offered_price']) . ". Expected Close: " . date('d M Y', strtotime($validated['neg_expected_close_date'])) . ". Remarks: " . ($validated['neg_remarks'] ?? 'None'),
        ]);

        return back()->with('success', 'Negotiation started successfully.');
    }

    public function recordBooking(Request $request, Lead $lead)
    {
        abort_if(Auth::user()->isSales(), 403, 'Sales Executives are not authorized to record bookings.');
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'booking_amount' => 'required|numeric',
            'booking_unit' => 'required|string',
            'total_unit_cost' => 'nullable|numeric',
            'booking_date' => 'required|date',
            'broker_id' => 'nullable|exists:brokers,id',
        ]);

        $lead->status = 'converted';
        $lead->save();

        \App\Models\Booking::create([
            'company_id' => Auth::user()->company_id,
            'booking_code' => 'BKG-' . strtoupper(Str::random(6)),
            'lead_id' => $lead->id,
            'customer_name' => trim($lead->first_name . ' ' . $lead->last_name),
            'customer_email' => $lead->email,
            'customer_phone' => $lead->phone,
            'project_id' => $validated['project_id'],
            'unit_id' => null, 
            'unit_identifier' => $validated['booking_unit'],
            'sales_user_id' => Auth::id(),
            'broker_id' => $validated['broker_id'] ?? null,
            'booking_amount' => $validated['booking_amount'],
            'total_unit_cost' => $validated['total_unit_cost'] ?? 0,
            'booking_date' => $validated['booking_date'],
            'status' => 'pending_approval',
            'approval_status' => 'pending'
        ]);

        \App\Models\LeadActivity::create([
            'company_id' => Auth::user()->company_id,
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'lead_converted',
            'description' => "Booking Recorded. Token: ₹" . number_format($validated['booking_amount']) . ". Unit: " . $validated['booking_unit'],
        ]);

        $managers = \App\Models\User::where('company_id', Auth::user()->company_id)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['admin', 'company_admin', 'manager', 'sales_manager', 'founder', 'director']);
            })->get();

        foreach ($managers as $manager) {
            app(\App\Services\NotificationService::class)->notify(
                $manager,
                'lead_converted',
                "🎉 Lead Converted!",
                "Lead {$lead->first_name} has booked unit {$validated['booking_unit']} with a token of ₹" . number_format($validated['booking_amount']) . ".",
                url("/leads/{$lead->id}")
            );
        }

        return back()->with('success', 'Booking recorded successfully.');
    }

    public function dropLead(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'lost_reason' => 'required|string',
            'lost_notes' => 'nullable|string',
        ]);

        $lead->status = 'lost';
        $lead->lost_reason = $validated['lost_reason'];
        $lead->save();

        \App\Models\LeadActivity::create([
            'company_id' => Auth::user()->company_id,
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'lead_lost',
            'description' => "Lead marked as dropped. Reason: {$validated['lost_reason']}. Notes: " . ($validated['lost_notes'] ?? 'None'),
        ]);

        $managers = \App\Models\User::where('company_id', Auth::user()->company_id)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['admin', 'company_admin', 'manager', 'sales_manager', 'founder', 'director']);
            })->get();

        foreach ($managers as $manager) {
            app(\App\Services\NotificationService::class)->notify(
                $manager,
                'lead_lost',
                "❌ Lead Lost",
                "Lead {$lead->first_name} was marked as lost due to: {$validated['lost_reason']}.",
                url("/leads/{$lead->id}")
            );
        }

        return back()->with('success', 'Lead marked as dropped.');
    }

    public function show($id, \App\Services\AiIntelligenceService $aiService)
    {
        $user = Auth::user();
        $lead = Lead::with(['assignedTo', 'broker', 'brokerLead', 'project', 'source', 'activities.user', 'calls.user', 'followUps', 'siteVisits.assignedTo'])->findOrFail($id);

        if ($user->isSales() && $lead->assigned_to_user_id !== $user->id) {
            return redirect()->route('leads.index')->with('error', 'Unauthorized. You can only access leads assigned to you.');
        }

        $isManagerTeamLead = $user->isManager()
            && $lead->assignedTo
            && $lead->assignedTo->company_id === $user->company_id
            && $lead->assignedTo->reporting_manager_id === $user->id;

        if ($user->isManager()
            && $lead->assigned_to_manager_id !== $user->id
            && !$isManagerTeamLead) {
            return redirect()->route('leads.index')->with('error', 'Unauthorized. You can only access leads in your manager pool.');
        }

        if ($user->isBroker()) {
            return redirect()->route('dashboard');
        }

        $aiScore = $aiService->calculateLeadScore($lead);
        $recommendations = $aiService->getSmartPropertyRecommendations($lead);
        $coaching = $aiService->generateSalesExecutiveCoaching($lead);

        $latestCall = $lead->calls->first();
        $callAnalysis = $aiService->generateCallSummaryAndSentiment($latestCall?->notes, $latestCall?->call_outcome);

        return view('leads.show', compact('lead', 'aiScore', 'recommendations', 'coaching', 'callAnalysis'));
    }

    public function destroy(Lead $lead, NotificationService $notificationService)
    {
        Gate::authorize('delete-leads');

        $currentUser = Auth::user();

        $leadCode     = $lead->lead_code;
        $customerName = "{$lead->first_name} {$lead->last_name}";

        // CRITICAL APPROVAL FLOW: If non-Director/Founder tries to delete a Lead, send approval request to Director
        if (!$currentUser->isDirectorOrFounder()) {
            SaasApprovalRequest::create([
                'company_id'           => $currentUser->company_id,
                'requested_by_user_id' => $currentUser->id,
                'action_type'          => 'delete_lead',
                'target_type'          => Lead::class,
                'target_id'            => $lead->id,
                'target_name'          => "{$leadCode} — {$customerName}",
                'payload'              => ['lead_id' => $lead->id],
                'reason'               => "Admin {$currentUser->name} requested deletion of lead '{$leadCode}' ({$customerName}).",
                'status'               => 'pending',
            ]);

            // Notify all Directors / Founders in this company
            $directors = User::where('company_id', $currentUser->company_id)
                ->whereHas('role', fn($q) => $q->whereIn('slug', ['director', 'founder']))
                ->get();

            foreach ($directors as $director) {
                $notificationService->notify(
                    $director,
                    'critical_approval_request',
                    "🚨 Critical Approval Needed: Delete Customer Lead",
                    "Admin {$currentUser->name} requested to DELETE lead '{$leadCode}' ({$customerName}). Please review and approve.",
                    route('company.approvals')
                );
            }

            return redirect()->route('leads.index')
                ->with('warning', "⚠️ Lead Deletion Request Submitted! Deleting a customer lead requires Company Director / Main Owner approval. Request sent for review.");
        }

        // Director / Founder: direct deletion
        $lead->delete();
        \App\Services\AuditLogService::log('lead_deleted', "Deleted Lead {$leadCode} ({$customerName}).", null);

        return redirect()->route('leads.index')->with('success', "Lead {$leadCode} ({$customerName}) deleted successfully.");
    }

    public function update(Request $request, Lead $lead)
    {
        Gate::authorize('manage-leads');

        $user = Auth::user();

        if ($user->isSales() && $lead->assigned_to_user_id !== $user->id) {
            return redirect()->route('leads.index')->with('error', 'Unauthorized. You can only update leads assigned to you.');
        }

        $isManagerTeamLead = $user->isManager()
            && $lead->assignedTo
            && $lead->assignedTo->company_id === $user->company_id
            && $lead->assignedTo->reporting_manager_id === $user->id;

        if ($user->isManager()
            && $lead->assigned_to_manager_id !== $user->id
            && !$isManagerTeamLead) {
            return redirect()->route('leads.index')->with('error', 'Unauthorized. You can only update leads in your manager pool.');
        }

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
