<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\BrokerCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class BookingApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isBroker()) {
            return response()->json(['error' => 'Brokers must use broker portal API endpoints.'], 403);
        }

        $query = Booking::with(['lead', 'unit', 'project', 'broker', 'salesUser']);

        if ($user->isSales()) {
            $query->where('sales_user_id', $user->id);
        }

        $bookings = $query->latest()->paginate(15);
        return response()->json($bookings);
    }

    public function store(Request $request, BookingService $bookingService)
    {
        $user = $request->user();

        if ($user->isBroker()) {
            return response()->json(['error' => 'Brokers cannot create internal bookings directly.'], 403);
        }

        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'lead_id' => 'required|exists:leads,id',
            'customer_name' => 'required|string|max:150',
            'customer_phone' => 'required|string|max:20',
            'booking_amount' => 'required|numeric|min:0',
            'broker_id' => 'nullable|exists:brokers,id',
        ]);

        $booking = $bookingService->createBooking($validated, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking created and unit locked successfully.',
            'booking' => $booking,
        ], 201);
    }

    public function approve(Request $request, Booking $booking, BrokerCommissionService $commissionService)
    {
        Gate::authorize('approve-bookings');

        abort_unless($booking->company_id === $request->user()->company_id, 404);
        abort_unless($booking->approval_status === 'pending', 422, 'Only pending bookings can be approved.');

        DB::transaction(function () use ($booking, $request) {
            $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedBooking->approval_status === 'pending', 422, 'Booking has already been processed.');
            $unit = $lockedBooking->unit()->lockForUpdate()->firstOrFail();
            abort_unless(in_array($unit->status, ['booking_pending', 'hold'], true), 422, 'Unit is no longer pending approval.');

            $lockedBooking->update([
                'status' => 'confirmed',
                'approval_status' => 'approved',
                'approved_by_user_id' => $request->user()->id,
                'approved_at' => now(),
            ]);
            $unit->update(['status' => 'booked']);
        });

        $commission = null;
        $booking->refresh();
        if ($booking->broker_id && !\App\Models\BrokerCommission::where('booking_id', $booking->id)->exists()) {
            $commission = $commissionService->generateCommission($booking);
            if ($commission) {
                $commissionService->approveCommission($commission, $request->user());
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Booking approved successfully.',
            'commission' => $commission,
        ]);
    }

    public function reject(Request $request, Booking $booking)
    {
        Gate::authorize('approve-bookings');
        abort_unless($booking->company_id === $request->user()->company_id, 404);

        $validated = $request->validate(['reason' => 'required|string|max:500']);
        abort_unless($booking->approval_status === 'pending', 422, 'Only pending bookings can be rejected.');

        DB::transaction(function () use ($booking, $validated) {
            $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $lockedBooking->update([
                'approval_status' => 'rejected',
                'status' => 'cancelled',
                'rejection_reason' => $validated['reason'],
            ]);
            $lockedBooking->unit()->update(['status' => 'available']);
        });

        return response()->json(['status' => 'success', 'message' => 'Booking rejected and unit released.']);
    }
}
