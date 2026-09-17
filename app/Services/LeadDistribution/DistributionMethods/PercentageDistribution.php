<?php

namespace App\Services\LeadDistribution\DistributionMethods;

use App\Models\DistributionRule;
use App\Models\DistributionMemberState;

class PercentageDistribution
{
    /**
     * Select a user based on the Deficit / Weighted Allocation algorithm.
     */
    public function selectUser(DistributionRule $rule, $eligibleMembers)
    {
        $selectedMember = null;
        $maxDeficit = -999999;

        // 1. Calculate active weight
        $totalActiveWeight = $eligibleMembers->sum('allocation_value');
        if ($totalActiveWeight <= 0) {
            return null;
        }

        // 2. Fetch or create member states for exactly this group and sum up total assigned.
        // By relying only on the member state instead of a global state, this automatically
        // adapts accurately to nested tiers (Managers vs Executives).
        $totalLeadsDistributed = 0;
        $memberStates = [];
        foreach ($eligibleMembers as $member) {
            $memberState = DistributionMemberState::firstOrCreate(
                ['distribution_rule_id' => $rule->id, 'user_id' => $member->user_id],
                ['actual_assigned_count' => 0]
            );
            $memberStates[$member->user_id] = $memberState;
            $totalLeadsDistributed += $memberState->actual_assigned_count;
        }

        // 3. Calculate expected vs actual for this specific subset of members
        foreach ($eligibleMembers as $member) {
            $normalizedPercentage = $member->allocation_value / $totalActiveWeight;
            $memberState = $memberStates[$member->user_id];

            $expectedLeads = $totalLeadsDistributed * $normalizedPercentage;
            $deficit = $expectedLeads - $memberState->actual_assigned_count;
            
            // Tie-breaker
            $weightedDeficit = $deficit + ($normalizedPercentage * 0.0001);

            if ($weightedDeficit > $maxDeficit) {
                $maxDeficit = $weightedDeficit;
                $selectedMember = $member;
            }
        }

        if ($selectedMember) {
            $selectedMemberState = $memberStates[$selectedMember->user_id];
            $selectedMemberState->increment('actual_assigned_count');
            return $selectedMember->user_id;
        }

        return null;
    }
}
