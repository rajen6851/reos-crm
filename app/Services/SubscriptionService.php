<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Str;

class SubscriptionService
{
    /**
     * Subscribe or upgrade company to a subscription plan
     */
    public function subscribe(
        Company $company,
        SubscriptionPlan $plan,
        string $paymentGateway = 'razorpay',
        ?string $transactionReference = null
    ): Subscription {
        $startsAt = now();
        $expiresAt = match ($plan->billing_cycle) {
            'yearly' => now()->addYear(),
            'quarterly' => now()->addMonths(3),
            default => now()->addDays(30),
        };

        // Update company tenant subscription details
        $company->update([
            'subscription_plan_id' => $plan->id,
            'subscription_expires_at' => $expiresAt,
            'status' => 'active',
        ]);

        // Create authoritative subscription history record
        $subscription = Subscription::create([
            'company_id' => $company->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => $startsAt,
            'ends_at' => $expiresAt,
            'status' => 'active',
            'payment_gateway' => $paymentGateway,
            'transaction_reference' => $transactionReference ?? 'TXN-SUB-' . strtoupper(Str::random(8)),
        ]);

        return $subscription;
    }

    /**
     * Renew active subscription for additional term
     */
    public function renew(
        Company $company,
        string $paymentGateway = 'razorpay',
        ?string $transactionReference = null
    ): Subscription {
        $plan = $company->subscriptionPlan ?? SubscriptionPlan::firstOrFail();

        $currentExpiry = ($company->subscription_expires_at && $company->subscription_expires_at->isFuture())
            ? $company->subscription_expires_at
            : now();

        $newExpiry = match ($plan->billing_cycle) {
            'yearly' => (clone $currentExpiry)->addYear(),
            'quarterly' => (clone $currentExpiry)->addMonths(3),
            default => (clone $currentExpiry)->addDays(30),
        };

        $company->update([
            'subscription_expires_at' => $newExpiry,
            'status' => 'active',
        ]);

        return Subscription::create([
            'company_id' => $company->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => $currentExpiry,
            'ends_at' => $newExpiry,
            'status' => 'active',
            'payment_gateway' => $paymentGateway,
            'transaction_reference' => $transactionReference ?? 'TXN-REN-' . strtoupper(Str::random(8)),
        ]);
    }
}
