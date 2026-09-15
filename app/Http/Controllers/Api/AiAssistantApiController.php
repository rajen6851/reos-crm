<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\AiIntelligenceService;
use Illuminate\Http\Request;

class AiAssistantApiController extends Controller
{
    public function __construct(
        protected AiIntelligenceService $aiService
    ) {}

    /**
     * Get AI Score, Conversion Probability, and Key Drivers for a Lead
     */
    public function leadScore(Request $request, int $id)
    {
        $lead = $this->authorizedLead($request, $id);
        $scoreData = $this->aiService->calculateLeadScore($lead);

        return response()->json([
            'status' => 'success',
            'lead_id' => $lead->id,
            'lead_code' => $lead->lead_code,
            'ai_score' => $scoreData,
        ]);
    }

    /**
     * Get Smart Property / Inventory Recommendations for a Lead
     */
    public function recommendations(Request $request, int $id)
    {
        $lead = $this->authorizedLead($request, $id);
        $recommendations = $this->aiService->getSmartPropertyRecommendations($lead);

        return response()->json([
            'status' => 'success',
            'lead_id' => $lead->id,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Generate AI Call Summary & Sentiment Analysis from raw notes
     */
    public function summarizeCall(Request $request)
    {
        $request->validate([
            'notes' => 'required|string',
            'call_outcome' => 'nullable|string',
        ]);

        $analysis = $this->aiService->generateCallSummaryAndSentiment(
            $request->notes,
            $request->call_outcome ?? 'connected'
        );

        return response()->json([
            'status' => 'success',
            'analysis' => $analysis,
        ]);
    }

    /**
     * Get AI Sales Coaching & Pitch Strategies
     */
    public function salesCoaching(Request $request, int $id)
    {
        $lead = $this->authorizedLead($request, $id);
        $coaching = $this->aiService->generateSalesExecutiveCoaching($lead);

        return response()->json([
            'status' => 'success',
            'lead_id' => $lead->id,
            'coaching' => $coaching,
        ]);
    }

    /**
     * Get AI Predictive Pipeline & Revenue Analytics
     */
    public function predictiveAnalytics(Request $request)
    {
        $user = $request->user();
        $analytics = $this->aiService->getPredictiveAnalytics($user->company_id ?? 1);

        return response()->json([
            'status' => 'success',
            'predictive_analytics' => $analytics,
        ]);
    }

    protected function authorizedLead(Request $request, int $id): Lead
    {
        $user = $request->user();
        $query = Lead::where('company_id', $user->company_id)->whereKey($id);

        if ($user->isSales()) {
            $query->where('assigned_to_user_id', $user->id);
        } elseif ($user->isManager()) {
            $teamIds = \App\Models\User::where('company_id', $user->company_id)
                ->where('reporting_manager_id', $user->id)
                ->pluck('id');
            $query->where(function ($leadQuery) use ($user, $teamIds) {
                $leadQuery->where('assigned_to_manager_id', $user->id)
                    ->orWhereIn('assigned_to_user_id', $teamIds);
            });
        } elseif ($user->isBroker()) {
            $brokerId = \App\Models\Broker::where('user_id', $user->id)->value('id');
            $query->where('broker_id', $brokerId);
        }

        return $query->firstOrFail();
    }
}
