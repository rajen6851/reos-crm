<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Broker;
use App\Models\BrokerCommission;

class CommissionService
{
    /**
     * Calculate and record broker commission upon booking confirmation/approval.
     */
    public function calculateAndRecordCommission(Booking $booking): ?BrokerCommission
    {
        if (!$booking->broker_id) {
            return null;
        }

        $broker = Broker::find($booking->broker_id);
        if (!$broker) {
            return null;
        }

        // Commission calculation: Default to broker rate percentage or 2% of total unit cost
        $rate = $broker->commission_rate > 0 ? $broker->commission_rate : 2.00;
        $totalCommission = ($booking->total_unit_cost * $rate) / 100;

        return BrokerCommission::create([
            'company_id' => $booking->company_id,
            'broker_id' => $broker->id,
            'booking_id' => $booking->id,
            'lead_id' => $booking->lead_id,
            'commission_type' => 'percentage',
            'rate_value' => $rate,
            'total_commission_amount' => $totalCommission,
            'status' => 'pending',
        ]);
    }
}
