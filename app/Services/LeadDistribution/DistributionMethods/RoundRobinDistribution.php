<?php

namespace App\Services\LeadDistribution\DistributionMethods;

use App\Models\DistributionRule;
use App\Models\DistributionMemberState;

class RoundRobinDistribution
{
    /**
     * Select a user based on the member with the lowest actual_assigned_count among the eligible members.
     * Tied members are processed in sequence by ID.
     */
    public function selectUser(DistributionRule $rule, $eligibleMembers)
    {
        $sortedMembers = $eligibleMembers->sortBy('id')->values();

        if ($sortedMembers->isEmpty()) {
            return null;
        }

        $minAssignedCount = null;
        $selectedMember = null;
        $memberStates = [];

        // Determine which member in THIS specific group is farthest behind in the robin cycle.
        // This makes the round robin completely isolated and safe for nested tiers.
        foreach ($sortedMembers as $member) {
            $memberState = DistributionMemberState::firstOrCreate(
                ['distribution_rule_id' => $rule->id, 'user_id' => $member->user_id],
                ['actual_assigned_count' => 0]
            );
            $memberStates[$member->user_id] = $memberState;

            if ($minAssignedCount === null || $memberState->actual_assigned_count < $minAssignedCount) {
                $minAssignedCount = $memberState->actual_assigned_count;
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
