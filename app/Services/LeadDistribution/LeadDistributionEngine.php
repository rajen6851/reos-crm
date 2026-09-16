<?php

namespace App\Services\LeadDistribution;

use App\Models\Lead;
use App\Models\DistributionRule;
use App\Models\DistributionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadDistributionEngine
{
    /**
     * Main entry point for lead distribution.
     * Takes a newly created lead and assigns it using the highest priority matching rule.
     */
    public function assignLead(Lead $lead)
    {
        // Use pessimistic locking on the lead to prevent duplicate assignment requests
        return DB::transaction(function () use ($lead) {
            $lead = Lead::lockForUpdate()->find($lead->id);

            // If already assigned via distribution (or manual), skip (Idempotency)
            // Assuming if assigned_to_user_id is already set, we don't redistribute unless forced
            if ($lead->assigned_to_user_id) {
                return $lead;
            }

            // 1. Find active matching rules sorted by priority
            $rule = $this->findMatchingRule($lead);

            if (!$rule) {
                Log::info("No distribution rule matched for lead {$lead->id}. Using default assignment.");
                // Fallback to company default logic if necessary (e.g. assign to company admin)
                return $lead;
            }
            
            // 3. Get eligible members
            $eligibleMembers = $this->getEligibleUsers($rule);
            
            $selectedUserId = $this->selectUser($rule, $eligibleMembers);

            if (!$selectedUserId) {
                return $lead; // Could not select user
            }

            // 4. Assign Lead
            $lead->assigned_to_user_id = $selectedUserId;
            
            // Also assign to the executive's manager if they have one
            $assignedUser = \App\Models\User::find($selectedUserId);
            if ($assignedUser && $assignedUser->reporting_manager_id) {
                $lead->assigned_to_manager_id = $assignedUser->reporting_manager_id;
            }
            
            $lead->save();

            // 5. Save Distribution Log
            DistributionLog::create([
                'lead_id' => $lead->id,
                'company_id' => $lead->company_id,
                'distribution_rule_id' => $rule->id,
                'assigned_user_id' => $selectedUserId,
                'assignment_type' => 'AUTO',
                // allocation_percentage can be added here if needed by fetching from member state
            ]);

            // 6. Send Notification (Placeholder)
            // event(new \App\Events\LeadAssigned($lead));

            return $lead;
        });
    }

    protected function findMatchingRule(Lead $lead)
    {
        // Fetch all active rules for this company, ordered by highest priority first (1 is highest usually, but let's assume ASC means higher priority 1 > 2)
        // Adjust the priority sorting based on standard (1 = highest priority)
        $rules = DistributionRule::where('company_id', $lead->company_id)
            ->where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get();

        foreach ($rules as $rule) {
            // Check conditions
            $matches = true;

            if ($rule->project_id && $rule->project_id != $lead->project_id) {
                $matches = false;
            }
            
            if ($matches && $rule->lead_source_id && $rule->lead_source_id != $lead->source_id) {
                $matches = false;
            }
            
            // Add more conditions here as needed (campaign_id, property_type, etc.)

            if ($matches) {
                return $rule;
            }
        }

        return null;
    }

    protected function getEligibleUsers(DistributionRule $rule)
    {
        // Load members who are active users
        return $rule->members()->whereHas('user', function ($q) {
            $q->where('is_active', true); 
            // In a real app, also check if user is on leave today, etc.
        })->get();
    }

    protected function selectUser(DistributionRule $rule, $eligibleMembers)
    {
        if ($rule->distribution_method === 'percentage') {
            return app(\App\Services\LeadDistribution\DistributionMethods\PercentageDistribution::class)->selectUser($rule, $eligibleMembers);
        }

        if ($rule->distribution_method === 'fixed_quantity') {
            return app(\App\Services\LeadDistribution\DistributionMethods\FixedQuantityDistribution::class)->selectUser($rule, $eligibleMembers);
        }

        if ($rule->distribution_method === 'performance') {
            return app(\App\Services\LeadDistribution\DistributionMethods\PerformanceDistribution::class)->selectUser($rule, $eligibleMembers);
        }

        if ($rule->distribution_method === 'round_robin') {
            return app(\App\Services\LeadDistribution\DistributionMethods\RoundRobinDistribution::class)->selectUser($rule, $eligibleMembers);
        }

        // Default fallback to round robin
        return app(\App\Services\LeadDistribution\DistributionMethods\RoundRobinDistribution::class)->selectUser($rule, $eligibleMembers);
    }
}
