<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isBroker()) {
            return redirect()->route('dashboard');
        }

        $query = Lead::whereIn('status', ['converted', 'booked'])
            ->with(['project', 'assignedTo', 'broker']);

        if ($user->isSales()) {
            $query->where('assigned_to_user_id', $user->id);
        }

        $customers = $query->latest()->get();

        $bookings = Booking::with(['unit', 'project', 'lead'])
            ->get()
            ->keyBy('lead_id');

        return view('customers.index', compact('customers', 'bookings'));
    }
}
