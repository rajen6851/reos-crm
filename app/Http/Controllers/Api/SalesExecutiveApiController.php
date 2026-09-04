<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\Unit;
use App\Services\BookingService;
use App\Services\DuplicateLeadService;
use App\Services\LeadService;
use App\Services\SiteVisitService;
use Illuminate\Http\Request;

class SalesExecutiveApiController extends Controller
{
    /**
     * Executive Dashboard Overview Stats
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if (!$user->isSales() && !$user->isCompanyAdmin() && !$user->isManager()) {
            return response()->json(['error' => 'Unauthorized access to sales dashboard.'], 403);
        }

        $assignedLeadsQuery = Lead::where('company_id', $user->company_id)
            ->where('assigned_to_user_id', $user->id);

        $totalAssignedLeads = (clone $assignedLeadsQuery)->count();
        $newLeads = (clone $assignedLeadsQuery)->where('status', 'new')->count();
        $inProgressLeads = (clone $assignedLeadsQuery)
            ->whereIn('status', ['assigned', 'contacted', 'follow_up', 'site_visit', 'site_visit_completed', 'interested', 'negotiation', 'booking_initiated'])
            ->count();
        $convertedLeads = (clone $assignedLeadsQuery)
            ->whereIn('status', ['converted', 'booked'])
            ->count();
        $lostLeads = (clone $assignedLeadsQuery)->where('status', 'lost')->count();

        $siteVisitsToday = SiteVisit::where('company_id', $user->company_id)
            ->where('assigned_to_user_id', $user->id)
            ->whereDate('scheduled_at', now()->toDateString())
            ->count();

        $siteVisitsUpcoming = SiteVisit::where('company_id', $user->company_id)
            ->where('assigned_to_user_id', $user->id)
            ->where('scheduled_at', '>', now())
            ->where('status', 'scheduled')
            ->count();

        $pendingFollowUps = FollowUp::where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $myBookingsCount = Booking::where('company_id', $user->company_id)
            ->where('sales_user_id', $user->id)
            ->count();

        return response()->json([
            'status' => 'success',
            'dashboard' => [
                'total_assigned_leads' => $totalAssignedLeads,
                'new_leads' => $newLeads,
                'in_progress_leads' => $inProgressLeads,
                'converted_leads' => $convertedLeads,
                'lost_leads' => $lostLeads,
                'site_visits_today' => $siteVisitsToday,
                'site_visits_upcoming' => $siteVisitsUpcoming,
                'pending_follow_ups' => $pendingFollowUps,
                'total_bookings' => $myBookingsCount,
            ]
        ]);
    }

    /**
     * Get assigned leads with filters & search
     */
    public function leads(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $query = Lead::where('company_id', $user->company_id)
            ->where('assigned_to_user_id', $user->id)
            ->with(['project', 'broker', 'source', 'brokerLead']);

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

        $leads = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $leads,
        ]);
    }

    /**
     * Get single lead details with timeline & activities
     */
    public function showLead(Request $request, int $id)
    {
        $user = $request->user();

        $lead = Lead::where('company_id', $user->company_id)
            ->where(function ($q) use ($user) {
                $q->where('assigned_to_user_id', $user->id)
                    ->orWhereRaw('? = 1', [$user->isCompanyAdmin() || $user->isManager() ? 1 : 0]);
            })
            ->where('id', $id)
            ->with(['project', 'broker', 'source', 'brokerLead', 'activities.user', 'siteVisits.project', 'followUps', 'calls'])
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $lead,
        ]);
    }

    /**
     * Create new lead directly from field by Sales Executive
     */
    public function storeLead(Request $request, DuplicateLeadService $duplicateService)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'source_id' => 'nullable|exists:lead_sources,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'interested_project_id' => 'nullable|exists:projects,id',
            'interested_unit_type' => 'nullable|string|max:50',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $duplicate = $duplicateService->findDuplicate($user->company_id, $validated['phone'], $validated['email'] ?? null);

        $lead = Lead::create(array_merge($validated, [
            'company_id' => $user->company_id,
            'lead_code' => 'LD-' . rand(10000, 99999),
            'status' => 'new',
            'assigned_to_user_id' => $user->id,
            'is_duplicate' => (bool)$duplicate,
            'duplicate_of_lead_id' => $duplicate?->id,
        ]));

        LeadActivity::create([
            'company_id' => $user->company_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'created_by_sales',
            'description' => 'Lead created in field by Sales Executive ' . $user->name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $duplicate ? 'Lead created with duplicate flag.' : 'Lead created and assigned to you successfully.',
            'is_duplicate' => (bool)$duplicate,
            'data' => $lead->fresh(['project', 'source', 'broker']),
        ], 201);
    }

    /**
     * Update status of an assigned lead & sync broker status
     */
    public function updateLeadStatus(Request $request, int $id, LeadService $leadService)
    {
        $user = $request->user();

        $lead = Lead::where('company_id', $user->company_id)
            ->where('id', $id)
            ->firstOrFail();

        if ($lead->assigned_to_user_id !== $user->id && !$user->isManager() && !$user->isCompanyAdmin()) {
            return response()->json(['error' => 'Not authorized to update this lead.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'lost_reason' => 'nullable|string',
        ]);

        if ($validated['status'] === 'lost') {
            $lead->update(['lost_reason' => $validated['lost_reason'] ?? $validated['notes'] ?? 'No reason provided']);
        }

        try {
            $leadService->updateStatus($lead, $validated['status'], $validated['notes'] ?? null, $user);

            // Email Notification to Sales Managers / Admins
            $managers = \App\Models\User::where('company_id', $user->company_id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('slug', ['admin', 'company_admin', 'manager', 'sales_manager', 'founder', 'director']);
                })
                ->where('id', '!=', $user->id)
                ->get();

            $execName = $user->name;
            $customerName = trim($lead->first_name . ' ' . $lead->last_name);
            $formattedStatus = strtoupper(str_replace('_', ' ', $validated['status']));

            $isNegotiation = ($validated['status'] === 'negotiation');
            $emailTitle = $isNegotiation 
                ? "🚨 URGENT: Lead Reached NEGOTIATION Stage - {$customerName}"
                : "📈 Lead Status Updated: {$customerName} → {$formattedStatus}";

            $emailMessage = $isNegotiation
                ? "Sales Executive {$execName} has advanced lead '{$customerName}' ({$lead->lead_code}) to the NEGOTIATION stage for project '{$lead->project?->name}'. Remarks: " . ($validated['notes'] ?? 'None') . ". Please review pricing/discount terms immediately and assist executive to finalize booking!"
                : "Sales Executive {$execName} updated status of lead '{$customerName}' ({$lead->lead_code}) to {$formattedStatus}. Remarks: " . ($validated['notes'] ?? 'None') . ". Review details on REOS to direct next steps.";

            foreach ($managers as $manager) {
                app(\App\Services\NotificationService::class)->notify(
                    $manager,
                    $isNegotiation ? 'lead_negotiation_stage' : 'lead_status_changed',
                    $emailTitle,
                    $emailMessage,
                    url("/leads/{$lead->id}")
                );
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lead status updated successfully and broker portal view synchronized.',
            'lead' => $lead->fresh(['project', 'broker', 'brokerLead']),
        ]);
    }

    /**
     * Add activity / interaction note to lead
     */
    public function addNote(Request $request, int $id)
    {
        $user = $request->user();

        $lead = Lead::where('company_id', $user->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'notes' => 'required|string',
            'activity_type' => 'nullable|string|max:50',
        ]);

        $activity = LeadActivity::create([
            'company_id' => $user->company_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => $validated['activity_type'] ?? 'sales_note',
            'description' => $validated['notes'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Note added to lead history.',
            'data' => $activity,
        ]);
    }

    /**
     * Get follow-ups or schedule new follow-up
     */
    public function followUps(Request $request, int $id)
    {
        $user = $request->user();

        $lead = Lead::where('company_id', $user->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $followUps = FollowUp::where('company_id', $user->company_id)
            ->where('lead_id', $lead->id)
            ->latest('scheduled_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $followUps,
        ]);
    }

    /**
     * Schedule follow up
     */
    public function scheduleFollowUp(Request $request, int $id)
    {
        $user = $request->user();

        $lead = Lead::where('company_id', $user->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'reminder_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $followUp = FollowUp::create([
            'company_id' => $user->company_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'scheduled_at' => $validated['scheduled_at'],
            'reminder_at' => $validated['reminder_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        // Automatically set lead status to follow_up if new/assigned/contacted
        if (in_array($lead->status, ['new', 'assigned', 'contacted'])) {
            $lead->update(['status' => 'follow_up']);
        }

        LeadActivity::create([
            'company_id' => $user->company_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'follow_up_scheduled',
            'description' => "Follow-up scheduled for {$followUp->scheduled_at->format('Y-m-d H:i')}",
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Follow-up scheduled successfully.',
            'data' => $followUp,
        ], 201);
    }

    /**
     * Site Visits assigned to executive
     */
    public function siteVisits(Request $request)
    {
        $user = $request->user();

        $query = SiteVisit::where('company_id', $user->company_id)
            ->where('assigned_to_user_id', $user->id)
            ->with(['lead', 'project']);

        if ($request->get('filter') === 'today') {
            $query->whereDate('scheduled_at', now()->toDateString());
        } elseif ($request->get('filter') === 'upcoming') {
            $query->where('scheduled_at', '>', now())->where('status', 'scheduled');
        } elseif ($request->get('filter') === 'completed') {
            $query->where('status', 'visited');
        }

        $siteVisits = $query->latest('scheduled_at')->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $siteVisits,
        ]);
    }

    /**
     * Schedule site visit
     */
    public function storeSiteVisit(Request $request, SiteVisitService $visitService)
    {
        $user = $request->user();

        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'scheduled_at' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'pickup_location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::where('company_id', $user->company_id)->where('id', $validated['lead_id'])->firstOrFail();

        $siteVisit = $visitService->scheduleVisit(array_merge($validated, [
            'assigned_to_user_id' => $user->id,
        ]), $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Site visit scheduled successfully.',
            'data' => $siteVisit->fresh(['lead', 'project']),
        ], 201);
    }

    /**
     * Update Site Visit status & outcome feedback
     */
    public function updateSiteVisitStatus(Request $request, int $id)
    {
        $user = $request->user();

        $siteVisit = SiteVisit::where('company_id', $user->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:scheduled,visited,completed,cancelled,no_show',
            'outcome' => 'nullable|string|max:100',
            'feedback_notes' => 'nullable|string',
        ]);

        $dbStatus = in_array($validated['status'], ['visited', 'completed']) ? 'completed' : $validated['status'];

        $updateData = [
            'status' => $dbStatus,
            'outcome' => $validated['outcome'] ?? $siteVisit->outcome,
            'feedback_notes' => $validated['feedback_notes'] ?? $siteVisit->feedback_notes,
        ];

        if (in_array($validated['status'], ['visited', 'completed']) && !$siteVisit->visited_at) {
            $updateData['visited_at'] = now();
            // Sync lead status
            $siteVisit->lead?->update(['status' => 'site_visit']);

            // Sync broker status
            if ($siteVisit->lead?->brokerLead) {
                $siteVisit->lead->brokerLead->update([
                    'broker_visible_status' => 'Site Visit Completed',
                    'site_visit_completed_at' => now(),
                ]);
            }
        }

        $siteVisit->update($updateData);

        LeadActivity::create([
            'company_id' => $user->company_id,
            'lead_id' => $siteVisit->lead_id,
            'user_id' => $user->id,
            'activity_type' => 'site_visit_updated',
            'description' => "Site visit updated to status: {$siteVisit->status}",
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Site visit status updated successfully.',
            'data' => $siteVisit->fresh(['lead', 'project']),
        ]);
    }

    /**
     * Get list of active projects for client presentation
     */
    public function projects(Request $request)
    {
        $user = $request->user();

        $projects = Project::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->withCount([
                'units as total_units_count',
                'units as available_units_count' => function ($q) {
                    $q->where('status', 'available');
                }
            ])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $projects,
        ]);
    }

    /**
     * Get inventory units for a project
     */
    public function projectUnits(Request $request, int $projectId)
    {
        $user = $request->user();

        $project = Project::where('company_id', $user->company_id)
            ->where('id', $projectId)
            ->firstOrFail();

        $unitsQuery = Unit::where('company_id', $user->company_id)
            ->where('project_id', $project->id);

        if ($request->filled('status')) {
            $unitsQuery->where('status', $request->status);
        }

        if ($request->filled('unit_type')) {
            $unitsQuery->where('unit_type', $request->unit_type);
        }

        $units = $unitsQuery->get();

        return response()->json([
            'status' => 'success',
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'code' => $project->code,
            ],
            'units' => $units,
        ]);
    }

    /**
     * Sales Executive Bookings list
     */
    public function bookings(Request $request)
    {
        $user = $request->user();

        $bookings = Booking::where('company_id', $user->company_id)
            ->where('sales_user_id', $user->id)
            ->with(['lead', 'unit', 'project', 'broker'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $bookings,
        ]);
    }

    /**
     * Create booking from field app (with pessimistic inventory lock)
     */
    public function createBooking(Request $request, BookingService $bookingService)
    {
        $user = $request->user();

        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'lead_id' => 'required|exists:leads,id',
            'customer_name' => 'required|string|max:150',
            'customer_phone' => 'required|string|max:20',
            'booking_amount' => 'required|numeric|min:0',
            'broker_id' => 'nullable|exists:brokers,id',
        ]);

        $booking = $bookingService->createBooking($validated, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking request submitted and unit locked successfully.',
            'data' => $booking->load(['unit', 'lead', 'project']),
        ], 201);
    }
}
