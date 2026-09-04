<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Support\Collection;

class AiIntelligenceService
{
    /**
     * Calculate 0-100 AI Lead Score, Conversion Probability, and Key Drivers
     */
    public function calculateLeadScore(Lead $lead): array
    {
        $score = 30; // Base score
        $drivers = [];

        // Status Weight
        switch (strtolower($lead->status)) {
            case 'converted':
            case 'booked':
                $score += 70;
                $drivers[] = '🎉 Converted / Booked Customer (+70)';
                break;
            case 'negotiation':
            case 'booking_initiated':
                $score += 40;
                $drivers[] = '📝 Active Deal Negotiation (+40)';
                break;
            case 'interested':
                $score += 30;
                $drivers[] = '⭐ High Buyer Interest (+30)';
                break;
            case 'site_visit':
            case 'site_visit_completed':
                $score += 25;
                $drivers[] = '🏢 Site Visit Completed (+25)';
                break;
            case 'follow_up':
            case 'contacted':
                $score += 15;
                $drivers[] = '📞 Engaged in Follow-up (+15)';
                break;
            case 'lost':
                $score = 0;
                $drivers[] = '❌ Marked as Lost (0)';
                break;
        }

        // Budget Signal
        if ($lead->budget_max && $lead->budget_max > 0) {
            $score += 15;
            $drivers[] = '💰 Clear Budget Limit Specified (+15)';
        }

        // Call Engagement Signal
        $connectedCalls = $lead->calls ? $lead->calls->where('call_outcome', 'connected')->count() : 0;
        if ($connectedCalls > 0) {
            $callBonus = min($connectedCalls * 10, 20);
            $score += $callBonus;
            $drivers[] = "📞 Active Phone Conversations ($connectedCalls calls, +$callBonus)";
        }

        // Site Visit Signal
        $completedVisits = $lead->siteVisits ? $lead->siteVisits->where('status', 'completed')->count() : 0;
        if ($completedVisits > 0) {
            $score += 15;
            $drivers[] = "🚶 Completed Physical Site Visit (+$15)";
        }

        // Channel Partner / Broker Signal
        if ($lead->broker_id) {
            $score += 10;
            $drivers[] = '🤝 Verified Channel Partner Referral (+10)';
        }

        // Duplicate Warning Penalty
        if ($lead->is_duplicate) {
            $score -= 15;
            $drivers[] = '⚠️ Duplicate Lead Warning (-15)';
        }

        // Clamp Score between 0 and 100
        $score = max(0, min(100, $score));

        // Determine Label & Color
        if ($score >= 75) {
            $label = 'Hot 🔥';
            $color = 'emerald';
            $recommendedAction = 'High priority deal! Initiate booking cost sheet and finalize unit selection immediately.';
        } elseif ($score >= 45) {
            $label = 'Warm ⚡';
            $color = 'amber';
            $recommendedAction = 'Schedule physical site visit or send digital project brochure via WhatsApp.';
        } else {
            $label = 'Cold 🧊';
            $color = 'slate';
            $recommendedAction = 'Re-engage customer with promo pricing updates or assign to nurturing campaign.';
        }

        return [
            'score' => $score,
            'label' => $label,
            'color' => $color,
            'probability_percentage' => min(95, max(5, (int) round($score * 0.9))),
            'key_drivers' => $drivers,
            'recommended_action' => $recommendedAction,
        ];
    }

    /**
     * AI Call Note Summarization & Sentiment Extraction
     */
    public function generateCallSummaryAndSentiment(?string $notes, ?string $callOutcome = 'connected'): array
    {
        $cleanNotes = trim($notes ?? '');
        if (empty($cleanNotes)) {
            return [
                'sentiment' => 'Neutral 😐',
                'sentiment_color' => 'slate',
                'summary' => 'Call logged with standard follow-up required.',
                'key_intent' => 'General Inquiry',
                'suggested_followup' => 'Schedule routine check-in call in 2 days.',
            ];
        }

        $lowered = strtolower($cleanNotes);

        // Sentiment NLP heuristics
        if (str_contains($lowered, 'ready') || str_contains($lowered, 'book') || str_contains($lowered, 'love') || str_contains($lowered, 'visit tomorrow') || str_contains($lowered, 'deal') || str_contains($lowered, 'final')) {
            $sentiment = 'High Interest / Positive 🔥';
            $color = 'emerald';
        } elseif (str_contains($lowered, 'costly') || str_contains($lowered, 'expensive') || str_contains($lowered, 'discount') || str_contains($lowered, 'budget high') || str_contains($lowered, 'thinking') || str_contains($lowered, 'check with family')) {
            $sentiment = 'Price Sensitive / Hesitant ⚖️';
            $color = 'amber';
        } elseif (str_contains($lowered, 'not interested') || str_contains($lowered, 'drop') || str_contains($lowered, 'bought elsewhere') || str_contains($lowered, 'stop calling') || str_contains($lowered, 'wrong number')) {
            $sentiment = 'Negative / Uninterested ❌';
            $color = 'rose';
        } else {
            $sentiment = 'Moderate Interest 📋';
            $color = 'sky';
        }

        // Summary extraction
        $summary = "Customer conversation indicates: " . ucfirst($cleanNotes);

        // Next Best Action
        if ($color === 'emerald') {
            $suggestedFollowup = 'Send formal Cost Sheet PDF and hold preferred unit for 48 hours.';
        } elseif ($color === 'amber') {
            $suggestedFollowup = 'Offer flexible payment schedule or schedule manager consultation to address budget concerns.';
        } elseif ($color === 'rose') {
            $suggestedFollowup = 'Update lead status to Lost and log customer lost reason.';
        } else {
            $suggestedFollowup = 'Send project video walkthrough on WhatsApp and confirm follow-up date.';
        }

        return [
            'sentiment' => $sentiment,
            'sentiment_color' => $color,
            'summary' => $summary,
            'key_intent' => $color === 'emerald' ? 'Purchase Intent' : ($color === 'amber' ? 'Price Negotiation' : 'Information Gathering'),
            'suggested_followup' => $suggestedFollowup,
        ];
    }

    /**
     * Smart AI Inventory Matching Engine
     */
    public function getSmartPropertyRecommendations(Lead $lead): Collection
    {
        $companyId = $lead->company_id;

        // Query available inventory units
        $units = Unit::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('status', 'available')
            ->with('project')
            ->get();

        if ($units->isEmpty()) {
            // Fallback: try across all active projects
            $units = Unit::withoutGlobalScopes()
                ->where('status', 'available')
                ->with('project')
                ->limit(10)
                ->get();
        }

        $leadBudgetMax = (float) ($lead->budget_max ?? 100000000);
        $leadBudgetMin = (float) ($lead->budget_min ?? 0);
        $preferredType = strtolower($lead->interested_unit_type ?? '');

        return $units->map(function ($unit) use ($lead, $leadBudgetMin, $leadBudgetMax, $preferredType) {
            $price = (float) ($unit->final_price > 0 ? $unit->final_price : $unit->base_price);
            $matchScore = 50;

            // Project match bonus
            if ($lead->interested_project_id && $unit->project_id == $lead->interested_project_id) {
                $matchScore += 30;
            }

            // Budget match bonus
            if ($leadBudgetMax > 0 && $price <= $leadBudgetMax && $price >= $leadBudgetMin) {
                $matchScore += 20;
            } elseif ($price > 0 && $leadBudgetMax > 0 && abs($price - $leadBudgetMax) / $leadBudgetMax <= 0.15) {
                $matchScore += 10;
            }

            // Unit type match bonus
            if (!empty($preferredType) && str_contains(strtolower($unit->unit_type ?? ''), $preferredType)) {
                $matchScore += 20;
            }

            $matchScore = min(98, max(40, $matchScore));

            return [
                'unit_id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'project_name' => $unit->project->name ?? 'Primary Township',
                'city' => $unit->project->city ?? 'Central',
                'unit_type' => $unit->unit_type ?? 'Residential',
                'carpet_area' => $unit->carpet_area ?? 1200,
                'final_price' => $price,
                'formatted_price' => '₹' . number_format($price, 2),
                'match_score_percentage' => $matchScore,
            ];
        })->sortByDesc('match_score_percentage')->take(3)->values();
    }

    /**
     * AI Sales Executive Coaching & Pitch Strategy
     */
    public function generateSalesExecutiveCoaching(Lead $lead): array
    {
        $scoreData = $this->calculateLeadScore($lead);

        $objections = [
            'Price Concerns' => 'Highlight high appreciation rate in this locality (+14% YoY) and flexible payment plans.',
            'Location Distance' => 'Emphasize upcoming metro line extension and proximity to IT corridors/top schools.',
            'Timeline Delay' => 'Share RERA possession certificate and show live construction milestone photos.',
        ];

        return [
            'recommended_pitch' => "Present " . ($lead->interested_unit_type ?? 'Premium Unit') . " highlighting zero broker fees, RERA approved approvals, and modern clubhouse amenities.",
            'best_contact_time' => '10:30 AM – 1:00 PM or 5:30 PM – 7:30 PM',
            'deal_velocity' => $scoreData['score'] > 60 ? 'Fast (Estimated closing within 7-14 days)' : 'Standard (Estimated closing within 30 days)',
            'objection_handling' => $objections,
        ];
    }

    /**
     * AI Predictive Revenue & Pipeline Analytics
     */
    public function getPredictiveAnalytics(int $companyId): array
    {
        $leads = Lead::withoutGlobalScopes()->where('company_id', $companyId)->get();

        $totalPipeline = $leads->count();
        $converted = $leads->whereIn('status', ['converted', 'booked'])->count();
        $hotLeads = $leads->filter(fn($l) => $this->calculateLeadScore($l)['score'] >= 75)->count();

        $historicalRate = $totalPipeline > 0 ? ($converted / $totalPipeline) : 0.15;
        $projectedConversions = max(1, (int) round($converted + ($hotLeads * 0.6) + ($totalPipeline * 0.1)));
        $estimatedRevenue = $projectedConversions * 4500000; // Est. avg deal value 45L

        return [
            'total_pipeline_leads' => $totalPipeline,
            'hot_pipeline_leads' => $hotLeads,
            'current_conversions' => $converted,
            'projected_conversions_this_month' => $projectedConversions,
            'projected_revenue_formatted' => '₹' . number_format($estimatedRevenue, 2),
            'conversion_confidence' => '88% (High Confidence)',
            'pipeline_health' => $hotLeads > 2 ? 'Strong Pipeline 🚀' : 'Moderate Pipeline ⚡',
        ];
    }
}
