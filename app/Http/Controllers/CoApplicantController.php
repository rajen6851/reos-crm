<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CoApplicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoApplicantController extends Controller
{
    public function store(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'pan_number' => 'nullable|string|max:20',
            'aadhar_number' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();

        CoApplicant::create([
            'company_id' => $user->company_id,
            'booking_id' => $booking->id,
            'full_name' => $request->full_name,
            'relationship' => $request->relationship,
            'phone' => $request->phone,
            'email' => $request->email,
            'pan_number' => strtoupper($request->pan_number),
            'aadhar_number' => $request->aadhar_number,
            'address' => $request->address,
        ]);

        return back()->with('status', 'Co-applicant / Joint buyer added successfully!');
    }

    public function destroy($id)
    {
        $coApplicant = CoApplicant::findOrFail($id);
        $coApplicant->delete();

        return back()->with('status', 'Co-applicant removed.');
    }
}
