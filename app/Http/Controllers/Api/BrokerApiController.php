<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrokerCommissionResource;
use App\Http\Resources\BrokerLeadResource;
use App\Http\Resources\BrokerPayoutResource;
use App\Models\Booking;
use App\Models\Broker;
use App\Models\BrokerCommission;
use App\Models\BrokerLead;
use App\Models\BrokerPayout;
use App\Models\Lead;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Services\BrokerLeadService;
use App\Services\BrokerPrivacyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BrokerApiController extends Controller
{
    public function __construct(
        protected BrokerLeadService $brokerLeadService,
        protected BrokerPrivacyService $privacyService
    ) {}

    protected function getBrokerForUser($user): ?Broker
    {
        if (!$user) {
            return null;
        }

        return Broker::withoutGlobalScopes()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if (!empty($user->email)) {
                    $q->orWhere('email', $user->email);
                }
                if (!empty($user->phone)) {
                    $q->orWhere('phone', $user->phone);
                }
            })
            ->first();
    }

    /**
     * Submit lead by Channel Partner / Broker
     */
    public function submitLead(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'project_id' => 'required|exists:projects,id',
            'property_type' => 'nullable|string|max:50',
            'unit_type' => 'nullable|string|max:50',
            'unit_id' => 'nullable|exists:units,id',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'preferred_location' => 'nullable|string|max:255',
            'requirement_notes' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'customer_type' => 'nullable|string|max:50',
        ]);

        $project = Project::withoutGlobalScopes()->find($validated['project_id']);
        if ($project && $project->visibility === 'private') {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected project is private and only available to internal company sales team.'
            ], 422);
        }

        $result = $this->brokerLeadService->submitBrokerLead($request->user(), $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Lead successfully submitted and waiting for manager review.',
            'lead_id' => $result['lead']->id,
            'broker_lead_id' => $result['broker_lead']->id,
            'is_duplicate' => $result['is_duplicate'],
            'data' => new BrokerLeadResource($result['lead']),
        ], 201);
    }

    /**
     * List all submitted broker leads
     */
    public function leads(Request $request)
    {
        $user = $request->user();
        $broker = $this->getBrokerForUser($user);

        if (!$broker) {
            return response()->json(['message' => 'Broker account not found.'], 404);
        }

        $leads = Lead::withoutGlobalScopes()
            ->where('broker_id', $broker->id)
            ->with(['brokerLead' => function ($q) {
                $q->withoutGlobalScopes();
            }, 'project' => function ($q) {
                $q->withoutGlobalScopes()->with('company');
            }])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'total_count' => $leads->count(),
            'data' => BrokerLeadResource::collection($leads),
        ]);
    }

    /**
     * Get single broker lead details (sanitized)
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();

        $brokerLead = BrokerLead::where('company_id', $user->company_id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('lead_id', $id);
            })
            ->firstOrFail();

        Gate::authorize('view', $brokerLead);

        $lead = $brokerLead->lead;

        return response()->json([
            'status' => 'success',
            'data' => new BrokerLeadResource($lead),
        ]);
    }

    /**
     * Get sanitized public timeline for lead
     */
    public function timeline(Request $request, int $id)
    {
        $user = $request->user();
        $brokerLead = BrokerLead::where('company_id', $user->company_id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('lead_id', $id);
            })
            ->firstOrFail();

        Gate::authorize('view', $brokerLead);

        return response()->json([
            'status' => 'success',
            'timeline' => $this->privacyService->sanitizeTimeline($brokerLead->lead),
        ]);
    }

    /**
     * Get site visits for lead
     */
    public function siteVisits(Request $request, int $id)
    {
        $user = $request->user();
        $brokerLead = BrokerLead::where('company_id', $user->company_id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('lead_id', $id);
            })
            ->firstOrFail();

        Gate::authorize('view', $brokerLead);

        $siteVisits = SiteVisit::where('company_id', $user->company_id)
            ->where('lead_id', $brokerLead->lead_id)
            ->latest('scheduled_at')
            ->get()
            ->map(function ($sv) {
                return [
                    'id' => $sv->id,
                    'project' => $sv->project?->name,
                    'scheduled_at' => $sv->scheduled_at->format('Y-m-d H:i:s'),
                    'status' => ucfirst(str_replace('_', ' ', $sv->status)),
                    'visited_at' => $sv->visited_at?->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'site_visits' => $siteVisits,
        ]);
    }

    /**
     * Get booking details for broker lead
     */
    public function booking(Request $request, int $id)
    {
        $user = $request->user();
        $brokerLead = BrokerLead::where('company_id', $user->company_id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('lead_id', $id);
            })
            ->firstOrFail();

        Gate::authorize('view', $brokerLead);

        $booking = Booking::where('company_id', $user->company_id)
            ->where('lead_id', $brokerLead->lead_id)
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'No booking record found for this lead.'], 444);
        }

        return response()->json([
            'status' => 'success',
            'booking' => $this->privacyService->sanitizeBooking($booking),
        ]);
    }

    /**
     * Get commissions summary and records
     */
    public function commissions(Request $request)
    {
        $user = $request->user();
        $broker = $this->getBrokerForUser($user);
        if (!$broker) {
            return response()->json(['message' => 'Broker account not found.'], 404);
        }

        $commissions = BrokerCommission::withoutGlobalScopes()
            ->where('broker_id', $broker->id)
            ->with(['booking' => function ($q) {
                $q->withoutGlobalScopes();
            }])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'total_commission' => $commissions->sum('total_commission_amount'),
            'approved_commission' => $commissions->whereIn('status', ['approved', 'ready_for_payout', 'paid'])->sum('total_commission_amount'),
            'pending_commission' => $commissions->where('status', 'pending')->sum('total_commission_amount'),
            'paid_commission' => $commissions->where('status', 'paid')->sum('total_commission_amount'),
            'data' => BrokerCommissionResource::collection($commissions),
        ]);
    }

    /**
     * Get payout history for broker
     */
    public function payouts(Request $request)
    {
        $user = $request->user();
        $broker = $this->getBrokerForUser($user);
        if (!$broker) {
            return response()->json(['message' => 'Broker account not found.'], 404);
        }

        $payouts = BrokerPayout::withoutGlobalScopes()
            ->where('broker_id', $broker->id)
            ->with(['commissions' => function ($q) {
                $q->withoutGlobalScopes();
            }])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'total_payout_amount' => $payouts->sum('amount_paid'),
            'data' => BrokerPayoutResource::collection($payouts),
        ]);
    }

    /**
     * Get broker notifications
     */
    public function notifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->limit(30)->get();

        return response()->json([
            'status' => 'success',
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => $notifications,
        ]);
    }

    /**
     * Get Broker App Dashboard Overview Metrics
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $broker = $this->getBrokerForUser($user);

        if (!$broker) {
            return response()->json(['message' => 'Broker account not found.'], 404);
        }

        $leadsQuery = Lead::withoutGlobalScopes()
            ->where('broker_id', $broker->id);

        $totalLeads = (clone $leadsQuery)->count();

        $activeLeads = (clone $leadsQuery)
            ->whereIn('status', ['new', 'assigned', 'contacted', 'follow_up', 'site_visit', 'site_visit_completed', 'interested', 'negotiation', 'booking_initiated'])
            ->count();

        $bookedLeads = (clone $leadsQuery)
            ->whereIn('status', ['converted', 'booked'])
            ->count();

        $commissions = BrokerCommission::withoutGlobalScopes()
            ->where('broker_id', $broker->id)
            ->get();

        $payouts = BrokerPayout::withoutGlobalScopes()
            ->where('broker_id', $broker->id)
            ->get();

        return response()->json([
            'status' => 'success',
            'dashboard' => [
                'broker_id' => $broker->id,
                'broker_name' => $broker->name ?? $user->name,
                'commission_rate' => $broker->commission_rate,
                'total_leads_submitted' => $totalLeads,
                'active_leads' => $activeLeads,
                'booked_leads' => $bookedLeads,
                'total_commission_earned' => (float)$commissions->sum('total_commission_amount'),
                'approved_commission' => (float)$commissions->whereIn('status', ['approved', 'ready_for_payout', 'paid'])->sum('total_commission_amount'),
                'pending_commission' => (float)$commissions->where('status', 'pending')->sum('total_commission_amount'),
                'total_payout_received' => (float)$payouts->sum('amount_paid'),
            ]
        ]);
    }

    /**
     * Get Projects Catalog for Broker referral
     */
    public function projects(Request $request)
    {
        $user = $request->user();

        $projects = Project::withoutGlobalScopes()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('visibility', 'public')->orWhereNull('visibility');
            })
            ->with(['company' => function ($q) {
                $q->withoutGlobalScopes();
            }])
            ->select(['id', 'company_id', 'name', 'code', 'location_address', 'city', 'state', 'rera_number', 'project_type', 'banner_image', 'visibility'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $projects,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.',
        ]);
    }
}
