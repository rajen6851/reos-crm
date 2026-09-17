<?php

namespace App\Services\LeadDistribution\DistributionMethods;

use App\Models\DistributionRule;
use App\Models\DistributionMemberState;

class FixedQuantityDistribution
{
    /**
     * Select a user based on fixed quantity allocation.
     * The rule assigns leads to members until their 'actual_assigned_count' reaches their 'allocation_value'.
     * Once a member reaches their quota, they are skipped.
     */
    public function selectUser(DistributionRule $rule, $eligibleMembers)
    {
        $validMembers = collect();
        $memberStates = [];

        // 1. Fetch valid members who haven't reached their quota
        foreach ($eligibleMembers as $member) {
            $memberState = DistributionMemberState::firstOrCreate(
                ['distribution_rule_id' => $rule->id, 'user_id' => $member->user_id],
                ['actual_assigned_count' => 0]
            );

            $quota = (int) $member->allocation_value;

            if ($memberState->actual_assigned_count < $quota) {
                $validMembers->push($member);
                $memberStates[$member->user_id] = $memberState;
            }
        }

        if ($validMembers->isEmpty()) {
            return null; // All limits reached in this tier
        }

        // 2. Distribute among valid members using isolated Round Robin (lowest count first)
        $sortedMembers = $validMembers->sortBy('id')->values();
        $minAssignedCount = null;
        $selectedMember = null;

        foreach ($sortedMembers as $member) {
            $memberState = $memberStates[$member->user_id];

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
