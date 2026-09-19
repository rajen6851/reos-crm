<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Call;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\Unit;
use App\Models\User;
use App\Services\BookingService;
use App\Services\DuplicateLeadService;
use App\Services\LeadService;
use App\Services\SiteVisitService;
use Illuminate\Http\Request;

class SalesExecutiveApiController extends Controller
{
    protected function teamExecutiveIds($user)
    {
        return User::where('company_id', $user->company_id)
            ->where('reporting_manager_id', $user->id)
            ->whereHas('role', fn ($query) => $query->whereIn('slug', ['sales_executive', 'executive']))
            ->pluck('id');
    }

    protected function managerLeadScope($query, $user): void
    {
        $teamExecutiveIds = $this->teamExecutiveIds($user);

        $query->where(function ($leadQuery) use ($user, $teamExecutiveIds) {
            $leadQuery->where('assigned_to_manager_id', $user->id)
                ->orWhereIn('assigned_to_user_id', $teamExecutiveIds);
        });
    }

    /**
     * Executive Dashboard Overview Stats
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if (!$user->isSales() && !$user->hasPermission('manage-leads') && !$user->isManager()) {
            return response()->json(['error' => 'Unauthorized access to sales dashboard.'], 403);
        }

        $isManager = $user->isManager();
        $teamExecutiveIds = $isManager
            ? $this->teamExecutiveIds($user)
            : collect([$user->id]);

        $assignedLeadsQuery = Lead::where('company_id', $user->company_id)
            ->when($isManager, function ($query) use ($user) {
                $this->managerLeadScope($query, $user);
            }, function ($query) use ($teamExecutiveIds) {
                $query->whereIn('assigned_to_user_id', $teamExecutiveIds);
            });

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
            ->whereIn('assigned_to_user_id', $teamExecutiveIds)
            ->whereDate('scheduled_at', now()->toDateString())
            ->count();

        $siteVisitsUpcoming = SiteVisit::where('company_id', $user->company_id)
            ->whereIn('assigned_to_user_id', $teamExecutiveIds)
            ->where('scheduled_at', '>', now())
            ->where('status', 'scheduled')
            ->count();

        $pendingFollowUps = FollowUp::where('company_id', $user->company_id)
            ->whereIn('user_id', $teamExecutiveIds)
            ->where('status', 'pending')
            ->count();

        $myBookingsCount = Booking::where('company_id', $user->company_id)
            ->whereIn('sales_user_id', $teamExecutiveIds)
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
                'scope' => $isManager ? 'team' : 'own',
                'team_executives_count' => $isManager ? $teamExecutiveIds->count() : 0,
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

        $assignedUserIds = $user->isManager()
            ? $this->teamExecutiveIds($user)
            : collect([$user->id]);

        $query = Lead::where('company_id', $user->company_id)
            ->when($user->isManager(), function ($leadQuery) use ($user) {
                $this->managerLeadScope($leadQuery, $user);
            }, function ($leadQuery) use ($assignedUserIds) {
                $leadQuery->whereIn('assigned_to_user_id', $assignedUserIds);
            })
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

        if ($request->filled('assigned_to_user_id') && $user->isManager()) {
            $query->where('assigned_to_user_id', $request->integer('assigned_to_user_id'));
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->date('created_from'));
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->date('created_to'));
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
                    ->when($user->isManager(), function ($managerQuery) use ($user) {
                        $this->managerLeadScope($managerQuery, $user);
                    })
                    ->when($user->hasPermission('manage-leads'), function ($adminQuery) {
                        $adminQuery->orWhereNotNull('id');
                    });
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

        app(\App\Services\LeadDistributionService::class)->distributeNewLead(
            $lead,
            app(\App\Services\NotificationService::class)
        );

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

        $managerCanAccess = $user->isManager()
            && (($lead->assigned_to_manager_id === $user->id)
                || $this->teamExecutiveIds($user)->contains($lead->assigned_to_user_id));

        if ($lead->assigned_to_user_id !== $user->id && !$managerCanAccess && !$user->hasPermission('manage-leads')) {
            return response()->json(['error' => 'Unauthorized. You can only update leads assigned to you.'], 403);
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
     * Assign lead to a team member (Manager only)
     */
    public function assignLead(Request $request, int $id)
    {
        $user = $request->user();

        if (!$user->isManager() && !$user->hasPermission('manage-users')) {
            return response()->json(['error' => 'Unauthorized. Only managers can view team metrics.'], 403);
        }

        $lead = Lead::where('company_id', $user->company_id)
            ->where('id', $id)
            ->firstOrFail();

        // Check if manager has access to this lead
        $managerCanAccess = $user->hasPermission('manage-leads') || 
            ($lead->assigned_to_manager_id === $user->id) || 
            $this->teamExecutiveIds($user)->contains($lead->assigned_to_user_id);

        if (!$managerCanAccess) {
            return response()->json(['error' => 'Not authorized to assign this lead.'], 403);
        }

        $validated = $request->validate([
            'assigned_to_user_id' => 'required|exists:users,id',
        ]);

        $assignedUser = User::where('company_id', $user->company_id)
            ->findOrFail($validated['assigned_to_user_id']);

        // Verify that the assigned user is in the manager's team
        if (!$user->hasPermission('manage-leads') && $assignedUser->reporting_manager_id !== $user->id && $assignedUser->id !== $user->id) {
            return response()->json(['error' => 'Unauthorized to view this user\'s timeline.'], 403);
        }

        $lead->update([
            'assigned_to_user_id' => $assignedUser->id,
            'assigned_to_manager_id' => $assignedUser->reporting_manager_id ?? null,
            'status' => 'assigned'
        ]);

        LeadActivity::create([
            'company_id' => $user->company_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'assigned',
            'description' => "Lead assigned to {$assignedUser->name} by {$user->name}",
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lead assigned successfully.',
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
            'customer_rating' => 'nullable|integer|min:1|max:5',
            'visit_images' => 'nullable|array|max:10',
            'visit_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $dbStatus = in_array($validated['status'], ['visited', 'completed']) ? 'completed' : $validated['status'];

        $updateData = [
            'status' => $dbStatus,
            'outcome' => $validated['outcome'] ?? $siteVisit->outcome,
            'feedback_notes' => $validated['feedback_notes'] ?? $siteVisit->feedback_notes,
            'customer_rating' => $validated['customer_rating'] ?? $siteVisit->customer_rating,
        ];

        if ($request->hasFile('visit_images')) {
            $imagePaths = $siteVisit->visit_images ?? [];
            foreach ($request->file('visit_images') as $image) {
                $imagePaths[] = $image->store('site-visit-images', 'public');
            }
            $updateData['visit_images'] = $imagePaths;
        }

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

    /**
     * Live duplicate lead detection before submission from mobile app
     */
    public function checkDuplicate(Request $request, DuplicateLeadService $duplicateService)
    {
        $user = $request->user();

        $validated = $request->validate([
            'phone' => 'required|string',
            'email' => 'nullable|email',
        ]);

        $duplicate = $duplicateService->findDuplicate($user->company_id, $validated['phone'], $validated['email'] ?? null);

        if (!$duplicate) {
            return response()->json([
                'status' => 'success',
                'is_duplicate' => false,
                'message' => 'No duplicate lead found.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'is_duplicate' => true,
            'duplicate_lead' => [
                'id' => $duplicate->id,
                'lead_code' => $duplicate->lead_code,
                'name' => trim($duplicate->first_name . ' ' . $duplicate->last_name),
                'phone' => $duplicate->phone,
                'email' => $duplicate->email,
                'status' => $duplicate->status,
                'assigned_to' => $duplicate->assignedToUser?->name ?? 'Unassigned',
                'created_at' => $duplicate->created_at->format('Y-m-d H:i:s'),
            ],
            'message' => 'Duplicate lead exists in system.',
        ]);
    }

    /**
     * Log calling activity (Connected, Not Connected, Busy, Callback Required, Missed Call)
     */
    public function logCall(Request $request, int $id)
    {
        $user = $request->user();

        $lead = Lead::where('company_id', $user->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'call_status' => 'required|in:connected,not_connected,busy,callback_required,missed_call',
            'duration_seconds' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'status' => 'nullable|string',
            'budget_max' => 'nullable|numeric',
            'sv_scheduled_at' => 'nullable|date|required_if:status,READY TO VISIT',
            'sv_project_id' => 'nullable|exists:projects,id|required_if:status,READY TO VISIT',
            'sv_assigned_to' => 'nullable|exists:users,id',
            'sv_broker_name' => 'nullable|string',
            'sv_broker_phone' => 'nullable|string',
            'sv_broker_company' => 'nullable|string',
            'sv_visit_description' => 'nullable|string',
            'sv_remark_1' => 'nullable|string',
            'sv_remark_2' => 'nullable|string',
            'next_followup_at' => 'nullable|date',
            'audio_recording' => 'nullable|file|mimes:mp3,wav,ogg,m4a,webm,aac,flac|max:51200',
        ]);

        if (isset($validated['budget_max'])) {
            $lead->budget_max = $validated['budget_max'];
            $lead->save();
        }

        $statusLabel = match ($validated['call_status']) {
            'connected' => 'Call Connected',
            'not_connected' => 'Call Not Connected',
            'busy' => 'Line Busy',
            'callback_required' => 'Callback Requested',
            'missed_call' => 'Missed Call Alert',
        };

        $audioPath = null;
        $audioName = null;
        if ($request->hasFile('audio_recording')) {
            $audio = $request->file('audio_recording');
            $audioPath = $audio->store('call-recordings', 'public');
            $audioName = $audio->getClientOriginalName();
        }

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

        $call = Call::create([
            'company_id' => $user->company_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'call_type' => 'outbound',
            'call_outcome' => $validated['call_status'],
            'notes' => $finalNotes ?: null,
            'audio_recording_path' => $audioPath,
            'audio_recording_name' => $audioName,
            'call_duration_seconds' => $validated['duration_seconds'] ?? 0,
            'called_at' => now(),
            'next_followup_at' => $validated['next_followup_at'] ?? null,
        ]);

        $activity = LeadActivity::create([
            'company_id' => $user->company_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'call_logged',
            'description' => "[$statusLabel] Duration: " . ($validated['duration_seconds'] ?? 0) . "s.\nNotes: " . ($finalNotes ?: 'None'),
        ]);

        if (!empty($validated['next_followup_at']) && (!isset($validated['status']) || ($validated['status'] !== 'Ready for Site Visit' && $validated['status'] !== 'Token Received' && $validated['status'] !== 'Not Interested'))) {
            FollowUp::create([
                'company_id' => $user->company_id,
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'scheduled_at' => $validated['next_followup_at'],
                'notes' => "Auto-scheduled after call status: $statusLabel",
                'status' => 'pending',
            ]);
        }

        // Handle Pipeline Stage Update & Site Visit Scheduling
        if (isset($validated['status'])) {
            if ($validated['status'] === 'Ready for Site Visit' || $validated['status'] === 'READY TO VISIT' || $validated['status'] === 'site_visit_scheduled') {
                $lead->status = 'site_visit';
                
                \App\Models\SiteVisit::create([
                    'company_id' => $user->company_id,
                    'lead_id' => $lead->id,
                    'project_id' => $validated['sv_project_id'],
                    'assigned_to_user_id' => $validated['sv_assigned_to'] ?? $user->id,
                    'scheduled_at' => $validated['sv_scheduled_at'],
                    'status' => 'scheduled',
                    'notes' => 'Site visit scheduled from API call log. ' . ($validated['notes'] ?? ''),
                    'broker_name' => $validated['sv_broker_name'] ?? null,
                    'broker_phone' => $validated['sv_broker_phone'] ?? null,
                    'broker_company' => $validated['sv_broker_company'] ?? null,
                    'visit_description' => $validated['sv_visit_description'] ?? null,
                    'remark_1' => $validated['sv_remark_1'] ?? null,
                    'remark_2' => $validated['sv_remark_2'] ?? null,
                ]);
                
                // Notify the assigned executive if it's someone else
                if (isset($validated['sv_assigned_to']) && $validated['sv_assigned_to'] != $user->id) {
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
            $newStatus = $lead->status;
            if ($validated['status'] === 'Token Received') {
                $newStatus = 'converted';
            } elseif ($validated['status'] === 'Not Interested' || $validated['status'] === 'dropped') {
                $newStatus = 'lost';
                if ($request->has('lost_reason')) {
                    $lead->lost_reason = $request->input('lost_reason');
                }
            } elseif ($validated['status'] === 'Warm / Negotiation' || $validated['status'] === 'WARM' || $validated['status'] === 'negotiation') {
                $newStatus = 'negotiation';
            } elseif ($validated['status'] === 'In Follow-up' || $validated['status'] === 'IN FOLLOWUP' || $validated['status'] === 'Meeting at Client Place') {
                $newStatus = 'follow_up';
            } elseif ($validated['status'] === 'Not Connected' || $validated['status'] === 'NOT CONNECTED') {
                if ($lead->status === 'new') {
                    $newStatus = 'contacted';
                }
            }

            if ($newStatus !== $lead->status) {
                app(\App\Services\LeadService::class)->updateStatus($lead, $newStatus, "Status updated via Mobile App log.", $user);
            }
            $lead->save();
        }
        // Email / Push Notification to Sales Managers & Admins when Sales Executive logs work
        $managers = User::where('company_id', $user->company_id)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['admin', 'company_admin', 'manager', 'sales_manager', 'founder', 'director']);
            })
            ->where('id', '!=', $user->id)
            ->get();

        $execName     = $user->name;
        $customerName = trim($lead->first_name . ' ' . $lead->last_name);
        $audioNote    = $audioPath ? " 🎙️ Audio recording attached." : "";
        $outcomeText  = $statusLabel;

        foreach ($managers as $manager) {
            app(\App\Services\NotificationService::class)->notify(
                $manager,
                'sales_activity_logged',
                "📞 Activity Update from {$execName} on {$customerName}",
                "Sales Executive {$execName} logged work on lead '{$customerName}' ({$lead->lead_code}). Outcome: {$outcomeText}. Remarks: " . ($finalNotes ?: 'None') . ".{$audioNote} Please review on REOS to direct next steps.",
                url("/leads/{$lead->id}")
            );
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Call logged successfully.',
            'data' => $activity->load('lead'),
            'call' => $call,
        ], 201);
    }

    /**
     * Record payment entry directly from mobile field app
     */
    public function recordPayment(Request $request, int $id)
    {
        $user = $request->user();

        $booking = Booking::where('company_id', $user->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|in:cash,cheque,upi,bank_transfer,card,online',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $payment = \App\Models\Payment::create([
            'company_id' => $user->company_id,
            'booking_id' => $booking->id,
            'receipt_number' => 'RCP-' . time() . '-' . rand(100, 999),
            'amount' => $validated['amount'],
            'payment_date' => now(),
            'payment_method' => $validated['payment_method'] === 'bank_transfer' ? 'net_banking' : $validated['payment_method'],
            'transaction_reference' => $validated['reference_number'] ?? null,
            'status' => 'cleared',
            'notes' => $validated['notes'] ?? 'Payment recorded via mobile app by ' . $user->name,
            'recorded_by_user_id' => $user->id,
            'created_by_user_id' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment entry recorded and receipt generated.',
            'data' => $payment,
        ], 201);
    }

    /**
     * Submit Agreement Skip Request from Mobile App (Approval Workflow)
     */
    public function requestAgreementSkip(Request $request, int $id)
    {
        $user = $request->user();

        $booking = Booking::where('company_id', $user->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $agreement = \App\Models\Agreement::updateOrCreate(
            ['company_id' => $user->company_id, 'booking_id' => $booking->id],
            [
                'agreement_number' => 'AGR-' . time(),
                'status' => 'skip_requested',
                'skip_requested_by_user_id' => $user->id,
                'skip_reason' => $validated['reason'],
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Agreement skip request submitted for Manager & Founder approval.',
            'data' => $agreement,
        ], 201);
    }

    /**
     * Geo-verify a site visit
     */
    public function verifyVisit(Request $request, $id)
    {
        $user = $request->user();

        $visit = \App\Models\SiteVisit::where('company_id', $user->company_id)
            ->where(function ($q) use ($user) {
                $q->where('assigned_to_user_id', $user->id)
                    ->when($user->isManager(), function ($managerQuery) use ($user) {
                        $teamIds = $user->teamExecutives()->pluck('id')->push($user->id);
                        $managerQuery->orWhereIn('assigned_to_user_id', $teamIds);
                    })
                    ->when($user->hasPermission('manage-leads'), function ($adminQuery) {
                        $adminQuery->orWhereNotNull('id');
                    });
            })
            ->where('id', $id)
            ->with('project')
            ->firstOrFail();

        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $projectLat = $visit->project?->latitude;
        $projectLng = $visit->project?->longitude;

        $distance = null;
        $isVerified = false;

        if ($projectLat && $projectLng) {
            $distance = \App\Helpers\GeoHelper::calculateDistance(
                $validated['latitude'],
                $validated['longitude'],
                $projectLat,
                $projectLng
            );
            
            // Allow 300 meters radius
            if ($distance !== null && $distance <= 300) {
                $isVerified = true;
            }
        }

        $photoPath = $visit->visit_photo_path;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('visit-photos', 'public');
        }

        $visit->update([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'distance_from_project' => $distance,
            'is_geo_verified' => $isVerified,
            'visit_photo_path' => $photoPath,
            'visited_at' => now(),
            'status' => 'conducted',
        ]);

        $msg = $isVerified 
            ? "Visit Verified ✅ (Distance: {$distance}m)" 
            : ($distance !== null ? "Not Verified ❌ (Distance: {$distance}m)" : "Verification pending: Project location missing.");

        return response()->json([
            'status' => 'success',
            'message' => $msg,
            'data' => $visit->fresh(),
        ]);
    }
}
