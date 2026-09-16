<?php

namespace App\Services\LeadDistribution\DistributionMethods;

use App\Models\DistributionRule;
use App\Models\DistributionMemberState;
use App\Models\DistributionState;

class PercentageDistribution
{
    /**
     * Select a user based on the Deficit / Weighted Allocation algorithm.
     */
    public function selectUser(DistributionRule $rule, $eligibleMembers)
    {
        // Get the overall state for this rule
        $ruleState = DistributionState::firstOrCreate(
            ['distribution_rule_id' => $rule->id],
            ['total_distributed' => 0]
        );

        $totalLeadsDistributed = $ruleState->total_distributed;

        $selectedMember = null;
        $maxDeficit = -999999;

        // We calculate total active weight (incase some users are inactive/on leave and we need fallback behavior)
        $totalActiveWeight = $eligibleMembers->sum('allocation_value');
        if ($totalActiveWeight <= 0) {
            return null;
        }

        foreach ($eligibleMembers as $member) {
            // Normalize the target percentage based on active users (Fallback logic)
            // If original total was 100, but an active user is missing, this recalculates to 100% among active users
            $normalizedPercentage = $member->allocation_value / $totalActiveWeight;

            // Load member state
            $memberState = DistributionMemberState::firstOrCreate(
                ['distribution_rule_id' => $rule->id, 'user_id' => $member->user_id],
                ['actual_assigned_count' => 0]
            );

            // Calculate Expected Leads
            $expectedLeads = $totalLeadsDistributed * $normalizedPercentage;

            // Calculate Deficit
            $deficit = $expectedLeads - $memberState->actual_assigned_count;

            // Add a small tie-breaker using original allocation_value just in case deficits are exactly equal
            // The one with the higher original percentage should win ties
            $weightedDeficit = $deficit + ($normalizedPercentage * 0.0001);

            if ($weightedDeficit > $maxDeficit) {
                $maxDeficit = $weightedDeficit;
                $selectedMember = $member;
            }
        }

        if ($selectedMember) {
            // Update states
            $ruleState->increment('total_distributed');
            
            $selectedMemberState = DistributionMemberState::where('distribution_rule_id', $rule->id)
                ->where('user_id', $selectedMember->user_id)
                ->first();
                
            $selectedMemberState->increment('actual_assigned_count');

            return $selectedMember->user_id;
        }

        return null;
    }
}
