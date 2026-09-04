<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\BrokerCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

        $booking->update([
            'status' => 'confirmed',
            'approval_status' => 'approved',
            'approved_by_user_id' => $request->user()->id,
            'approved_at' => now(),
        ]);

        $booking->unit->update(['status' => 'booked']);

        $commission = null;
        if ($booking->broker_id) {
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
}
