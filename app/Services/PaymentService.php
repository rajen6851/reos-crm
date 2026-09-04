<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Record payment, generate receipt number, and update booking status.
     */
    public function recordPayment(array $data, User $user): Payment
    {
        $booking = Booking::findOrFail($data['booking_id']);

        $receiptNumber = 'RCP-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $payment = Payment::create([
            'company_id' => $user->company_id,
            'booking_id' => $booking->id,
            'payment_schedule_id' => $data['payment_schedule_id'] ?? null,
            'receipt_number' => $receiptNumber,
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'] ?? now(),
            'payment_method' => $data['payment_method'] ?? 'net_banking',
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'status' => 'cleared',
            'recorded_by_user_id' => $user->id,
            'cleared_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        return $payment;
    }
}
