<?php

namespace App\Services\LeadDistribution\DistributionMethods;

use App\Models\DistributionRule;
use App\Models\DistributionMemberState;
use App\Models\Lead;
use App\Models\SiteVisit;
use App\Models\Booking;

class PerformanceDistribution
{
    /**
     * Select a user based on dynamic performance scoring.
     */
    public function selectUser(DistributionRule $rule, $eligibleMembers)
    {
        $memberScores = [];
        $totalScore = 0;
        
        $thirtyDaysAgo = now()->subDays(30);

        foreach ($eligibleMembers as $member) {
            $userId = $member->user_id;
            
            // Base score so everyone has a chance (even new employees)
            $score = 10; 
            
            // +1 for every lead handled in last 30 days
            $leadsHandled = Lead::where('assigned_to_user_id', $userId)
                ->where('company_id', $rule->company_id)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count();
            $score += ($leadsHandled * 1);
            
            // +5 for every site visit done
            $siteVisits = SiteVisit::whereHas('lead', function($q) use ($userId) {
                    $q->where('assigned_to_user_id', $userId);
                })
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->whereIn('status', ['visited', 'completed'])
                ->count();
            $score += ($siteVisits * 5);
            
            // +20 for every booking
            $bookings = Booking::where('company_id', $rule->company_id)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->whereHas('lead', function($q) use ($userId) {
                    $q->where('assigned_to_user_id', $userId);
                })
                ->count();
            $score += ($bookings * 20);

            $memberScores[$userId] = $score;
            $totalScore += $score;
        }

        if ($totalScore == 0) {
            // Fallback if something weird happens
            return app(RoundRobinDistribution::class)->selectUser($rule, $eligibleMembers);
        }

        $bestUserId = null;
        $highestDeficit = -INF;
        
        // Get rule state
        $ruleState = $rule->states()->firstOrCreate(
            ['company_id' => $rule->company_id],
            ['last_assigned_user_id' => null, 'total_leads_distributed' => 0]
        );
        $totalDistributed = $ruleState->total_leads_distributed + 1;

        foreach ($eligibleMembers as $member) {
            $userId = $member->user_id;
            
            $dynamicPercentage = ($memberScores[$userId] / $totalScore) * 100;
            
            $memberState = DistributionMemberState::firstOrCreate(
                ['distribution_rule_id' => $rule->id, 'user_id' => $userId],
                ['leads_assigned' => 0, 'deficit' => 0]
            );

            // Deficit = Expected - Actual
            $expected = $totalDistributed * ($dynamicPercentage / 100);
            $deficit = $expected - $memberState->leads_assigned;
            
            if ($deficit > $highestDeficit) {
                $highestDeficit = $deficit;
                $bestUserId = $userId;
            }
        }
        
        if ($bestUserId) {
            $ruleState->last_assigned_user_id = $bestUserId;
            $ruleState->increment('total_leads_distributed');
            
            $selectedState = DistributionMemberState::where('distribution_rule_id', $rule->id)
                                ->where('user_id', $bestUserId)->first();
            $selectedState->increment('leads_assigned');
            $selectedState->deficit = $highestDeficit;
            $selectedState->save();
        }

        return $bestUserId;
    }
}
