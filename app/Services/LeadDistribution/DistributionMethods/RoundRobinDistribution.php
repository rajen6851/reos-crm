<?php

namespace App\Services\LeadDistribution\DistributionMethods;

use App\Models\DistributionRule;
use App\Models\DistributionState;

class RoundRobinDistribution
{
    /**
     * Select a user based on persistent Round Robin sequence.
     */
    public function selectUser(DistributionRule $rule, $eligibleMembers)
    {
        // Sort members consistently, e.g., by ID to maintain a strict sequence
        $sortedMembers = $eligibleMembers->sortBy('id')->values();

        if ($sortedMembers->isEmpty()) {
            \Log::info("RoundRobinDistribution: eligible members is empty!");
            return null;
        }

        $ruleState = DistributionState::firstOrCreate(
            ['distribution_rule_id' => $rule->id],
            ['total_distributed' => 0]
        );

        $lastAssignedUserId = $ruleState->last_assigned_user_id;
        \Log::info("RoundRobinDistribution: lastAssignedUserId is " . json_encode($lastAssignedUserId));
        
        $nextMember = null;

        if (!$lastAssignedUserId) {
            // No one assigned yet, pick the first one
            $nextMember = $sortedMembers->first();
        } else {
            // Find the index of the last assigned user in our currently active sorted members
            $lastIndex = $sortedMembers->search(function ($item) use ($lastAssignedUserId) {
                return $item->user_id == $lastAssignedUserId;
            });

            if ($lastIndex !== false) {
                // Pick the next one in the array
                $nextIndex = $lastIndex + 1;
                
                // If we reach the end of the array, loop back to 0
                if ($nextIndex >= $sortedMembers->count()) {
                    $nextIndex = 0;
                }
                
                $nextMember = $sortedMembers[$nextIndex];
            } else {
                // If last assigned user is no longer eligible (e.g. inactive),
                // just pick the first eligible member to restart sequence cleanly among active members.
                $nextMember = $sortedMembers->first();
            }
        }

        if ($nextMember) {
            // Update states
            $ruleState->last_assigned_user_id = $nextMember->user_id;
            $ruleState->increment('total_distributed');
            \Log::info("RoundRobinDistribution: nextMember selected is " . $nextMember->user_id);
            return $nextMember->user_id;
        }
        
        \Log::info("RoundRobinDistribution: nextMember is null for some reason!");
        return null;
    }
}
