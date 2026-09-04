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
        $lead = Lead::findOrFail($data['lead_id']);

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
