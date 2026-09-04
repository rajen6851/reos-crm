<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionApiController extends Controller
{
    /**
     * Get available Subscription Plans
     */
    public function plans(Request $request)
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();

        return response()->json([
            'status' => 'success',
            'data' => $plans,
        ]);
    }

    /**
     * Get current Company Subscription Status & Usage Summary
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'No company associated with user.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'subscription' => $company->usageSummary(),
            'history' => $company->subscriptions()->with('subscriptionPlan')->limit(10)->get(),
        ]);
    }

    /**
     * Subscribe or Upgrade Company Plan
     */
    public function subscribe(Request $request, SubscriptionService $subService)
    {
        $user = $request->user();

        if (!$user->isCompanyAdmin() && !$user->isSaaSFounder()) {
            return response()->json(['error' => 'Only company admin or founder can manage subscription.'], 403);
        }

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'payment_gateway' => 'nullable|string|max:50',
            'transaction_reference' => 'nullable|string|max:100',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $company = $user->company;

        $subscription = $subService->subscribe(
            $company,
            $plan,
            $validated['payment_gateway'] ?? 'razorpay',
            $validated['transaction_reference'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'message' => "Successfully subscribed company to {$plan->name}.",
            'subscription' => $subscription,
            'company_summary' => $company->fresh()->usageSummary(),
        ]);
    }

    /**
     * Renew active company subscription
     */
    public function renew(Request $request, SubscriptionService $subService)
    {
        $user = $request->user();

        if (!$user->isCompanyAdmin() && !$user->isSaaSFounder()) {
            return response()->json(['error' => 'Only company admin can renew subscription.'], 403);
        }

        $validated = $request->validate([
            'payment_gateway' => 'nullable|string|max:50',
            'transaction_reference' => 'nullable|string|max:100',
        ]);

        $company = $user->company;

        $subscription = $subService->renew(
            $company,
            $validated['payment_gateway'] ?? 'razorpay',
            $validated['transaction_reference'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription renewed successfully.',
            'subscription' => $subscription,
            'company_summary' => $company->fresh()->usageSummary(),
        ]);
    }
}
