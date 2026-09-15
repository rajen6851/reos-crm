<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\SiteVisit;
use App\Models\User;

class SiteVisitService
{
    /**
     * Schedule a site visit for a lead.
     */
    public function scheduleVisit(array $data, User $user): SiteVisit
    {
        $lead = Lead::where('company_id', $user->company_id)
            ->whereKey($data['lead_id'])
            ->firstOrFail();

        if ($user->isSales() && $lead->assigned_to_user_id !== $user->id) {
            abort(403, 'You can only schedule visits for your assigned leads.');
        }

        if ($user->isManager()) {
            $isTeamLead = $lead->assigned_to_manager_id === $user->id
                || User::whereKey($lead->assigned_to_user_id)
                    ->where('reporting_manager_id', $user->id)
                    ->exists();
            if (!$isTeamLead) {
                abort(403, 'You can only schedule visits for your team leads.');
            }
        }

        if (!empty($data['assigned_to_user_id'])) {
            User::where('company_id', $user->company_id)
                ->whereKey($data['assigned_to_user_id'])
                ->whereHas('role', fn ($query) => $query->whereIn('slug', ['sales_executive', 'executive']))
                ->firstOrFail();
        }

        $siteVisit = SiteVisit::create([
            'company_id' => $user->company_id,
            'lead_id' => $lead->id,
            'project_id' => $data['project_id'] ?? $lead->interested_project_id,
            'unit_id' => $data['unit_id'] ?? null,
            'assigned_to_user_id' => $data['assigned_to_user_id'] ?? $user->id,
            'scheduled_at' => $data['scheduled_at'],
            'status' => 'scheduled',
            'pickup_location' => $data['pickup_location'] ?? null,
            'feedback_notes' => $data['notes'] ?? null,
        ]);

        // Update lead status to site_visit
        $lead->update(['status' => 'site_visit']);

        LeadActivity::create([
            'company_id' => $user->company_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'site_visit_scheduled',
            'description' => "Site visit scheduled for {$siteVisit->scheduled_at}",
        ]);

        return $siteVisit;
    }
}
