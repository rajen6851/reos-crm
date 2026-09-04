<?php

namespace App\Services;

use App\Events\BrokerPayoutProcessed;
use App\Models\BrokerCommission;
use App\Models\BrokerPayout;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BrokerPayoutService
{
    public function processPayout(
        int $brokerId,
        array $commissionIds,
        User $processedBy,
        array $payoutData
    ): BrokerPayout {
        return DB::transaction(function () use ($brokerId, $commissionIds, $processedBy, $payoutData) {
            $commissions = BrokerCommission::where('company_id', $processedBy->company_id)
                ->where('broker_id', $brokerId)
                ->whereIn('id', $commissionIds)
                ->whereIn('status', ['approved', 'ready_for_payout'])
                ->get();

            if ($commissions->isEmpty()) {
                throw new InvalidArgumentException("No eligible approved commissions found for payout.");
            }

            $totalAmount = $commissions->sum('total_commission_amount');
            $payoutCode = 'PAY-' . strtoupper(Str::random(8));

            $payout = BrokerPayout::create([
                'company_id' => $processedBy->company_id,
                'broker_id' => $brokerId,
                'payout_code' => $payoutCode,
                'amount_paid' => $totalAmount,
                'payout_date' => now(),
                'payment_method' => $payoutData['payment_method'] ?? 'bank_transfer',
                'transaction_reference' => $payoutData['transaction_reference'] ?? null,
                'remarks' => $payoutData['remarks'] ?? null,
                'status' => 'completed',
                'processed_by_user_id' => $processedBy->id,
            ]);

            // Attach commissions in pivot table
            $payout->commissions()->attach($commissions->pluck('id')->toArray());

            // Mark commissions as paid
            BrokerCommission::whereIn('id', $commissions->pluck('id'))->update([
                'status' => 'paid',
            ]);

            event(new BrokerPayoutProcessed($payout, $commissions));

            return $payout;
        });
    }
}
