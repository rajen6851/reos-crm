<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\SiteVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteVisitController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isBroker()) {
            return redirect()->route('dashboard');
        }

        $query = Lead::whereIn('status', ['site_visit', 'negotiation', 'converted', 'booked'])
            ->with(['project', 'assignedTo', 'siteVisits' => function ($q) {
                $q->latest()->with(['feedbackBy']);
            }]);

        $logsQuery = LeadActivity::where('activity_type', 'site_visit_logged')
            ->with(['lead', 'user']);

        if ($user->isSales()) {
            $query->where('assigned_to_user_id', $user->id);
            $logsQuery->where('user_id', $user->id);
        } elseif ($user->isManager()) {
            $query->where('assigned_to_manager_id', $user->id);
        }

        // --- Apply Filters ---
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('assigned_by')) {
            $query->where('assigned_to_user_id', $request->assigned_by);
        }
        if ($request->filled('project_id')) {
            $query->where('interested_project_id', $request->project_id);
        }
        if ($request->filled('inquiry_status')) {
            $query->where('status', $request->inquiry_status);
        }
        if ($request->filled('broker')) {
            $query->where('broker_id', $request->broker);
        }
        
        // Site Visit Specific Filters (Using whereHas to filter Leads that have site visits matching the criteria)
        if ($request->filled('site_visited_by') || $request->filled('visit_from') || $request->filled('visit_to')) {
            $query->whereHas('siteVisits', function ($q) use ($request) {
                if ($request->filled('site_visited_by')) {
                    $q->where('assigned_to_user_id', $request->site_visited_by);
                }
                if ($request->filled('visit_from')) {
                    $q->whereDate('scheduled_at', '>=', $request->visit_from);
                }
                if ($request->filled('visit_to')) {
                    $q->whereDate('scheduled_at', '<=', $request->visit_to);
                }
            });
        }
        
        // City Filter (Assuming city is on Project)
        if ($request->filled('city')) {
            $query->whereHas('project', function ($q) use ($request) {
                $q->where('city', $request->city);
            });
        }

        $siteVisits      = $query->latest()->get();
        $recentVisitLogs = $logsQuery->latest()->take(15)->get();
        
        $projects = \App\Models\Project::where('company_id', $user->company_id)->get();
        $users = \App\Models\User::where('company_id', $user->company_id)->get();
        $brokers = \App\Models\Broker::where('company_id', $user->company_id)->get();
        
        // Pluck unique cities/states/etc if we need dynamic filters for them
        $cities = \App\Models\Project::where('company_id', $user->company_id)->whereNotNull('city')->pluck('city')->unique();

        return view('site_visits.index', compact('siteVisits', 'recentVisitLogs', 'projects', 'users', 'brokers', 'cities'));
    }

    /**
     * Store site visit feedback + images (submitted by Sales Executive or Manager).
     * Handles: text feedback, star rating, up to 10 visit images, outcome, lead status sync.
     */
    public function storeFeedback(Request $request, Lead $lead)
    {
        $request->validate([
            'feedback_notes'  => 'required|string|min:5|max:2000',
            'next_action'     => 'required|string',
            'customer_rating' => 'nullable|integer|min:1|max:5',
            'visit_images'    => 'nullable|array|max:10',
            'visit_images.*'  => 'image|mimes:jpg,jpeg,png,webp|max:5120', // 5MB each
        ]);

        $user = Auth::user();

        // Find or create the latest site_visit record for this lead
        $siteVisit = SiteVisit::where('lead_id', $lead->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Upload images
        $imagePaths = $siteVisit?->visit_images ?? [];
        if ($request->hasFile('visit_images')) {
            foreach ($request->file('visit_images') as $image) {
                if ($image->isValid()) {
                    $filename    = 'sv_' . $lead->id . '_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $imagePaths[] = $image->storeAs('site-visit-images', $filename, 'public');
                }
            }
        }

        // Outcome → lead status mapping
        $newLeadStatus = match ($request->next_action) {
            'negotiation'      => 'negotiation',
            'booking_drafted'  => 'negotiation',
            'dropped'          => 'lost',
            default            => $lead->status, // keep current status
        };

        // Map form next_action → valid DB ENUM value for `outcome` column
        // DB enum: ('interested', 'follow_up_required', 'not_interested', 'booking_initiated')
        $dbOutcome = match ($request->next_action) {
            'follow_up_scheduled' => 'follow_up_required',
            'negotiation'         => 'interested',
            'booking_drafted'     => 'booking_initiated',
            'dropped'             => 'not_interested',
            default               => 'follow_up_required',
        };

        // Update or create SiteVisit record
        if ($siteVisit) {
            $siteVisit->update([
                'status'                => 'completed',
                'visited_at'            => now(),
                'feedback_notes'        => $request->feedback_notes,
                'outcome'               => $dbOutcome,
                'customer_rating'       => $request->customer_rating,
                'visit_images'          => $imagePaths,
                'visit_feedback_by'     => $user->id,
                'feedback_submitted_at' => now(),
            ]);
        } else {
            // Create new if none exists (shouldn't normally happen but graceful)
            $siteVisit = SiteVisit::create([
                'company_id'            => $user->company_id,
                'lead_id'               => $lead->id,
                'project_id'            => $lead->interested_project_id,
                'assigned_to_user_id'   => $lead->assigned_to_user_id ?? $user->id,
                'scheduled_at'          => now(),
                'visited_at'            => now(),
                'status'                => 'completed',
                'feedback_notes'        => $request->feedback_notes,
                'outcome'               => $dbOutcome,
                'customer_rating'       => $request->customer_rating,
                'visit_images'          => $imagePaths,
                'visit_feedback_by'     => $user->id,
                'feedback_submitted_at' => now(),
            ]);
        }

        // Sync to Google Calendar
        \App\Jobs\SyncCalendarEventJob::dispatch($siteVisit);

        // Update lead status if changed
        if ($newLeadStatus !== $lead->status) {
            $lead->update(['status' => $newLeadStatus]);
        }

        // Activity log
        $ratingText  = $request->customer_rating ? " | Rating: {$request->customer_rating}/5 ⭐" : '';
        $imageCount  = count($imagePaths);
        $imagesText  = $imageCount > 0 ? " | {$imageCount} photo(s) uploaded" : '';

        LeadActivity::create([
            'company_id'    => $user->company_id,
            'lead_id'       => $lead->id,
            'user_id'       => $user->id,
            'activity_type' => 'site_visit_logged',
            'description'   => "Site visit feedback submitted by {$user->name}.{$ratingText}{$imagesText} Next action: " . ucwords(str_replace('_', ' ', $request->next_action)) . ". Notes: " . substr($request->feedback_notes, 0, 120),
            'metadata'      => [
                'outcome'         => $request->next_action,
                'customer_rating' => $request->customer_rating,
                'image_count'     => $imageCount,
            ],
        ]);

        \App\Services\AuditLogService::log(
            'site_visit_feedback',
            "Feedback submitted for site visit of lead {$lead->lead_code} by {$user->name}. Rating: {$request->customer_rating}/5.",
            $lead,
            null,
            ['outcome' => $request->next_action, 'images' => $imageCount]
        );

        return back()->with('success', "✅ Site visit feedback saved! {$imageCount} image(s) uploaded. Lead status updated.");
    }
}
