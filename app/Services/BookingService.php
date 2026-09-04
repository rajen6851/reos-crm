<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CostSheet;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Create a booking with pessimistic row lock to prevent double booking.
     */
    public function createBooking(array $data, User $user): Booking
    {
        return DB::transaction(function () use ($data, $user) {
            // 1. Lock Unit row for update
            $unit = Unit::where('id', $data['unit_id'])
                ->where('company_id', $user->company_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($unit->status, ['available', 'hold'])) {
                throw new Exception("Unit {$unit->unit_number} is not available for booking. Current status: {$unit->status}");
            }

            $lead = Lead::find($data['lead_id']);
            $brokerId = $data['broker_id'] ?? $lead?->broker_id;

            // 2. Create Cost Sheet if not already passed
            $costSheetId = $data['cost_sheet_id'] ?? null;
            if (!$costSheetId) {
                $baseCost = $unit->final_price > 0 ? $unit->final_price : $unit->base_price;
                $costSheet = CostSheet::create([
                    'company_id' => $user->company_id,
                    'project_id' => $unit->project_id,
                    'unit_id' => $unit->id,
                    'base_cost' => $baseCost,
                    'plc_cost' => $data['plc_cost'] ?? 0,
                    'parking_cost' => $data['parking_cost'] ?? 0,
                    'statutory_charges' => $data['statutory_charges'] ?? ($baseCost * 0.05), // 5% GST stub
                    'other_charges' => $data['other_charges'] ?? 0,
                    'total_cost' => $baseCost + ($data['plc_cost'] ?? 0) + ($data['parking_cost'] ?? 0) + ($data['statutory_charges'] ?? ($baseCost * 0.05)),
                    'payment_plan_type' => $data['payment_plan_type'] ?? 'construction_linked',
                    'created_by_user_id' => $user->id,
                ]);
                $costSheetId = $costSheet->id;
                $totalCost = $costSheet->total_cost;
            } else {
                $costSheet = CostSheet::findOrFail($costSheetId);
                $totalCost = $costSheet->total_cost;
            }

            // 3. Create Booking
            $booking = Booking::create([
                'company_id' => $user->company_id,
                'booking_code' => 'BK-' . strtoupper(Str::random(6)),
                'lead_id' => $data['lead_id'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'],
                'project_id' => $unit->project_id,
                'unit_id' => $unit->id,
                'sales_user_id' => $user->id,
                'broker_id' => $brokerId,
                'cost_sheet_id' => $costSheetId,
                'booking_amount' => $data['booking_amount'],
                'total_unit_cost' => $totalCost,
                'booking_date' => now(),
                'status' => 'pending_approval',
                'approval_status' => 'pending',
            ]);

            // 4. Update Unit Status atomically
            $unit->update(['status' => 'booking_pending']);

            // 5. Create Lead Activity Log
            LeadActivity::create([
                'company_id' => $user->company_id,
                'lead_id' => $data['lead_id'],
                'user_id' => $user->id,
                'activity_type' => 'booking_created',
                'description' => "Booking initiated for Unit {$unit->unit_number} with booking code {$booking->booking_code}.",
                'metadata' => ['booking_id' => $booking->id, 'amount' => $data['booking_amount']],
            ]);

            return $booking;
        });
    }
}
