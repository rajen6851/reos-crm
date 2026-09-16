<?php

namespace App\Services\LeadDistribution\DistributionMethods;

use App\Models\DistributionRule;
use App\Models\DistributionMemberState;
use Illuminate\Support\Facades\DB;

class FixedQuantityDistribution
{
    /**
     * Select a user based on fixed quantity allocation.
     * The rule assigns leads to members until their 'leads_assigned' reaches their 'allocation_value'.
     * Once a member reaches their quota, they are skipped.
     */
    public function selectUser(DistributionRule $rule, $eligibleMembers)
    {
        // Get or create state for rule to track last assigned user (for round robin among eligible users)
        $ruleState = $rule->states()->firstOrCreate(
            ['company_id' => $rule->company_id],
            ['last_assigned_user_id' => null, 'total_leads_distributed' => 0]
        );

        // Filter eligible members: only those who haven't reached their allocation value
        $validMembers = [];
        $memberStates = [];

        foreach ($eligibleMembers as $member) {
            $memberState = DistributionMemberState::firstOrCreate(
                [
                    'distribution_rule_id' => $rule->id,
                    'user_id' => $member->user_id,
                ],
                [
                    'leads_assigned' => 0,
                    'deficit' => 0,
                ]
            );

            // allocation_value acts as the quota
            $quota = (int) $member->allocation_value;

            if ($memberState->leads_assigned < $quota) {
                $validMembers[] = $member;
                $memberStates[$member->user_id] = $memberState;
            }
        }

        if (empty($validMembers)) {
            // All members have reached their fixed quantity quota.
            // Depending on requirements, we could either reset quotas or stop assigning.
            // We will return null to indicate this rule can no longer satisfy assignments.
            return null;
        }

        // Apply Round Robin among the remaining valid members
        $lastAssignedId = $ruleState->last_assigned_user_id;
        
        $selectedUserId = null;

        if (!$lastAssignedId) {
            // First time, select the first valid member
            $selectedUserId = $validMembers[0]->user_id;
        } else {
            // Find the index of the last assigned user in the valid members array
            $lastIndex = -1;
            foreach ($validMembers as $index => $member) {
                if ($member->user_id == $lastAssignedId) {
                    $lastIndex = $index;
                    break;
                }
            }

            // Select the next user in the array (wrap around if at the end)
            if ($lastIndex !== -1 && isset($validMembers[$lastIndex + 1])) {
                $selectedUserId = $validMembers[$lastIndex + 1]->user_id;
            } else {
                // If last user was not found or was the last in array, wrap to first
                $selectedUserId = $validMembers[0]->user_id;
            }
        }

        // Update state
        $ruleState->last_assigned_user_id = $selectedUserId;
        $ruleState->increment('total_leads_distributed');

        $selectedState = $memberStates[$selectedUserId];
        $selectedState->increment('leads_assigned');

        return $selectedUserId;
    }
}
