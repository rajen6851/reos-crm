<?php

namespace App\Services;

use App\Events\BrokerCommissionApproved;
use App\Events\BrokerCommissionGenerated;
use App\Models\Booking;
use App\Models\Broker;
use App\Models\BrokerCommission;
use App\Models\BrokerLead;
use App\Models\CostSheet;
use App\Models\Lead;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BrokerCommissionService
{
    /**
     * Auto-calculate commission on booking confirmation
     */
    public function generateCommission(Booking $booking): ?BrokerCommission
    {
        if (!$booking->broker_id) {
            return null;
        }

        return DB::transaction(function () use ($booking) {
            $broker = Broker::where('id', $booking->broker_id)->first();

            if (!$broker) {
                return null;
            }

            // Check if commission already generated for this booking
            $existing = BrokerCommission::withoutGlobalScopes()
                ->where('booking_id', $booking->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            // Calculate rate: percentage or fixed
            $rate = $broker->commission_rate > 0 ? $broker->commission_rate : 2.50;
            $commissionAmount = ($booking->total_unit_cost * $rate) / 100;

            $commission = BrokerCommission::withoutGlobalScopes()->create([
                'company_id' => $booking->company_id,
                'broker_id' => $broker->id,
                'booking_id' => $booking->id,
                'lead_id' => $booking->lead_id,
                'commission_type' => 'percentage',
                'rate_value' => $rate,
                'total_commission_amount' => $commissionAmount,
                'status' => 'pending',
            ]);

            event(new BrokerCommissionGenerated($commission));

            return $commission;
        });
    }

    /**
     * Auto-heal / Ensure commission exists for a booked broker lead
     */
    public function ensureCommissionForBrokerLead(BrokerLead $brokerLead): ?BrokerCommission
    {
        return DB::transaction(function () use ($brokerLead) {
            $existing = BrokerCommission::withoutGlobalScopes()
                ->where('lead_id', $brokerLead->lead_id)
                ->first();

            if ($existing) {
                return $existing;
            }

            $lead = Lead::withoutGlobalScopes()->find($brokerLead->lead_id);
            if (!$lead) {
                return null;
            }

            $broker = Broker::find($brokerLead->broker_id);
            if (!$broker) {
                return null;
            }

            // Check if a Booking record exists
            $booking = Booking::withoutGlobalScopes()
                ->where('lead_id', $brokerLead->lead_id)
                ->first();

            if (!$booking) {
                // Find or pick a unit for project
                $unit = Unit::withoutGlobalScopes()
                    ->where('project_id', $brokerLead->project_id)
                    ->first();

                $unitPrice = $unit ? ($unit->final_price > 0 ? $unit->final_price : $unit->base_price) : 7800000.00;

                // Create Cost Sheet
                $costSheet = CostSheet::withoutGlobalScopes()->create([
                    'company_id' => $brokerLead->company_id,
                    'project_id' => $brokerLead->project_id,
                    'unit_id' => $unit?->id ?? 1,
                    'base_cost' => $unitPrice,
                    'total_cost' => $unitPrice,
                    'created_by_user_id' => $broker->user_id ?? 1,
                ]);

                // Create Booking
                $booking = Booking::withoutGlobalScopes()->create([
                    'company_id' => $brokerLead->company_id,
                    'booking_code' => 'BK-' . strtoupper(Str::random(6)),
                    'lead_id' => $lead->id,
                    'customer_name' => trim("{$lead->first_name} {$lead->last_name}"),
                    'customer_email' => $lead->email,
                    'customer_phone' => $lead->phone,
                    'project_id' => $brokerLead->project_id,
                    'unit_id' => $unit?->id ?? 1,
                    'sales_user_id' => $broker->user_id ?? 1,
                    'broker_id' => $broker->id,
                    'cost_sheet_id' => $costSheet->id,
                    'booking_amount' => $unitPrice * 0.10, // 10% booking amount
                    'total_unit_cost' => $unitPrice,
                    'booking_date' => now(),
                    'status' => 'confirmed',
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                ]);
            } else {
                if (!$booking->broker_id) {
                    $booking->update(['broker_id' => $broker->id]);
                }
            }

            $commission = $this->generateCommission($booking);
            if ($commission && $booking->approval_status === 'approved' && $commission->status === 'pending') {
                $commission->update([
                    'status' => 'ready_for_payout',
                    'approved_at' => now(),
                ]);
            }

            return $commission;
        });
    }

    public function approveCommission(BrokerCommission $commission, User $user): BrokerCommission
    {
        if ($user->role_id && !$user->is_super_admin && !$user->isCompanyAdmin() && !$user->hasPermission('process-payouts') && !$user->hasPermission('manage-commissions')) {
            throw new InvalidArgumentException("User {$user->name} is not authorized to approve commissions.");
        }

        if ($commission->status === 'paid') {
            throw new InvalidArgumentException("Commission {$commission->commission_code} is already paid.");
        }

        $commission->update([
            'status' => 'ready_for_payout',
            'approved_at' => now(),
        ]);

        event(new BrokerCommissionApproved($commission, $user));

        return $commission;
    }
}
