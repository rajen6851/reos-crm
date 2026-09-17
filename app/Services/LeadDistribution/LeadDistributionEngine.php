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

            $assignedUser = \App\Models\User::find($selectedUserId);

            // If the selected user is a Manager, route to their team!
            if ($assignedUser && $assignedUser->isManager()) {
                // Find the parent member record to see if there are nested executives
                $parentMember = $rule->members()->where('user_id', $assignedUser->id)->whereNull('parent_member_id')->first();
                
                $executiveId = $this->assignToManagerTeam($rule, $assignedUser, $parentMember);
                
                if ($executiveId && $executiveId !== $assignedUser->id) {
                    $lead->assigned_to_manager_id = $assignedUser->id; // The manager is the originally selected user
                    $selectedUserId = $executiveId;
                    $assignedUser = \App\Models\User::find($selectedUserId); // Refresh assigned user to be the executive
                } else {
                    // No executives available, manager keeps it
                    $lead->assigned_to_manager_id = $assignedUser->id;
                }
            } else {
                // It was an executive from the start
                if ($assignedUser && $assignedUser->reporting_manager_id) {
                    $lead->assigned_to_manager_id = $assignedUser->reporting_manager_id;
                }
            }

            // 4. Assign Lead
            $lead->assigned_to_user_id = $selectedUserId;
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
        // Load members who are active users (only parent members for the first tier)
        return $rule->members()
            ->whereNull('parent_member_id')
            ->whereHas('user', function ($q) {
                $q->where('is_active', true); 
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

    protected function assignToManagerTeam(DistributionRule $rule, \App\Models\User $manager, $parentMember = null)
    {
        // 1. Check if the manager has explicitly nested executives in the rule
        if ($parentMember) {
            $nestedMembers = $rule->members()
                ->where('parent_member_id', $parentMember->id)
                ->whereHas('user', function ($q) {
                    $q->where('is_active', true); 
                })->get();

            if ($nestedMembers->isNotEmpty()) {
                // We have explicit nested allocations! We should select a user from them using the rule's method
                // Note: To be fully recursive we could call selectUser again, but we just need an ID
                $selectedUserId = $this->selectUser($rule, $nestedMembers);
                if ($selectedUserId) {
                    return $selectedUserId;
                }
            }
        }

        // 2. Fallback: No specific nested % were defined in the rule, so we just use dynamic load-balancing for ALL reporting executives
        $executives = \App\Models\User::where('company_id', $manager->company_id)
            ->where('reporting_manager_id', $manager->id)
            ->where('is_active', true)
            ->get();

        if ($executives->isEmpty()) {
            return $manager->id;
        }

        $selectedExecutive = $executives->sortBy(function ($executive) {
            $executiveStats = Lead::where('company_id', $executive->company_id)
                ->where('assigned_to_user_id', $executive->id)
                ->selectRaw('COUNT(*) as assigned_count, MAX(id) as latest_assignment_id')
                ->first();

            return [
                (int) $executiveStats->assigned_count,
                (int) ($executiveStats->latest_assignment_id ?? 0),
            ];
        })->first();

        return $selectedExecutive->id;
    }
}
