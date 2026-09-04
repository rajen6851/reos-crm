<?php

namespace App\Http\Controllers;

use App\Events\BrokerBookingConfirmed;
use App\Models\Agreement;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use App\Services\BookingService;
use App\Services\BrokerCommissionService;
use App\Services\LeadService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    public function index()
    {
        if (Auth::user()->isBroker()) {
            return redirect()->route('dashboard');
        }

        $bookings = Booking::with(['lead', 'project', 'unit', 'salesUser', 'agreement', 'payments'])->latest()->paginate(10);
        $leads = Lead::whereIn('status', ['interested', 'negotiation', 'site_visit', 'new'])->get();
        $units = Unit::whereIn('status', ['available', 'hold'])->get();
        $availableUnits = $units;
        $projects = Project::all();

        return view('bookings.index', compact('bookings', 'leads', 'units', 'availableUnits', 'projects'));
    }

    public function store(Request $request, BookingService $bookingService, LeadService $leadService)
    {
        if (Auth::user()->isBroker()) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'lead_id' => 'required|exists:leads,id',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email',
            'booking_amount' => 'required|numeric|min:1',
            'agreement_value' => 'nullable|numeric|min:1',
            'is_agreement_skipped' => 'nullable|boolean',
            'plc_cost' => 'nullable|numeric|min:0',
            'parking_cost' => 'nullable|numeric|min:0',
            'payment_plan_type' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);

        $data = array_merge([
            'customer_name' => trim($lead->first_name . ' ' . $lead->last_name),
            'customer_phone' => $lead->phone,
            'customer_email' => $lead->email,
        ], array_filter($validated));

        try {
            $booking = $bookingService->createBooking($data, Auth::user());
            $leadService->updateStatus($lead, 'converted', "Booking created with code {$booking->booking_code}.", Auth::user());

            // Initialize Agreement record
            Agreement::create([
                'company_id' => Auth::user()->company_id,
                'booking_id' => $booking->id,
                'agreement_number' => 'AGR-' . rand(10000, 99999),
                'status' => 'pending_draft',
            ]);

            // Email Notification to Company Admins & Managers
            $admins = User::where('company_id', Auth::user()->company_id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('slug', ['admin', 'company_admin', 'founder', 'director', 'manager', 'sales_manager']);
                })->get();

            foreach ($admins as $admin) {
                app(\App\Services\NotificationService::class)->notify(
                    $admin,
                    'booking_created',
                    "🎉 New Property Booking #{$booking->booking_code}",
                    "A new property unit booking {$booking->booking_code} for customer '{$booking->customer_name}' was created by " . Auth::user()->name . ".",
                    url("/bookings")
                );
            }

            // Official Email Notification to Customer
            $customerEmail = $booking->customer_email ?? $lead->email;
            if (!empty($customerEmail)) {
                $unitNumber = $booking->unit->unit_number ?? 'N/A';
                $projectName = $booking->project->name ?? 'Real Estate Project';
                app(\App\Services\NotificationService::class)->sendDirectEmail(
                    $customerEmail,
                    $booking->customer_name,
                    "🎉 Property Unit Booking Registered: {$booking->booking_code}",
                    "Congratulations! Your booking request for Unit {$unitNumber} in '{$projectName}' has been registered under Booking Code {$booking->booking_code}. Amount Paid: ₹" . number_format($booking->booking_amount, 2) . ". Our team will confirm your booking shortly.",
                    url("/bookings/{$booking->id}/receipt")
                );
            }

            return redirect()->route('bookings.index')->with('success', "Booking {$booking->booking_code} created successfully! Unit locked.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(Booking $booking, BrokerCommissionService $commissionService)
    {
        Gate::authorize('approve-bookings');

        $booking->update([
            'approval_status' => 'approved',
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
            'status' => 'confirmed',
        ]);

        $booking->unit->update(['status' => 'booked']);

        // Fire Booking Confirmed Event
        event(new BrokerBookingConfirmed($booking));

        // Official Booking Confirmation Email to Customer
        $customerEmail = $booking->customer_email ?? $booking->lead?->email;
        if (!empty($customerEmail)) {
            $unitNumber = $booking->unit->unit_number ?? 'N/A';
            $projectName = $booking->project->name ?? 'Real Estate Project';
            app(\App\Services\NotificationService::class)->sendDirectEmail(
                $customerEmail,
                $booking->customer_name,
                "🎉 Official Property Booking Confirmed: Unit {$unitNumber} ({$booking->booking_code})",
                "Congratulations! Your booking for Unit {$unitNumber} in '{$projectName}' has been OFFICIALLY APPROVED & CONFIRMED! Your property unit is now officially locked in your name. Total Unit Price: ₹" . number_format($booking->agreed_price, 2) . ".",
                url("/bookings/{$booking->id}/receipt")
            );
        }

        // Generate Commission
        if ($booking->broker_id) {
            $commission = $commissionService->generateCommission($booking);
            if ($commission) {
                $commissionService->approveCommission($commission, Auth::user());
                
                // Notify Broker User via Email
                if ($booking->broker && $booking->broker->user) {
                    app(\App\Services\NotificationService::class)->notify(
                        $booking->broker->user,
                        'commission_generated',
                        "💰 Commission Generated: ₹" . number_format($commission->total_commission_amount, 2),
                        "Congratulations! Your referral booking {$booking->booking_code} has been approved. A commission of ₹" . number_format($commission->total_commission_amount, 2) . " has been generated.",
                        url("/brokers")
                    );
                }
            }
        }

        return back()->with('success', "Booking {$booking->booking_code} APPROVED! Broker commission generated.");
    }

    public function reject(Booking $booking)
    {
        Gate::authorize('approve-bookings');

        $booking->update([
            'approval_status' => 'rejected',
            'status' => 'cancelled',
        ]);

        if ($booking->unit) {
            $booking->unit->update(['status' => 'available']);
        }

        if ($booking->lead) {
            $booking->lead->update(['status' => 'negotiation']);
        }

        \App\Services\AuditLogService::log('booking_rejected', "Booking {$booking->booking_code} was rejected. Unit and lead unlocked.", $booking);

        return back()->with('success', "Booking {$booking->booking_code} REJECTED. Unit released back to Available.");
    }

    public function requestAgreementSkip(Request $request, Agreement $agreement)
    {
        $request->validate(['skip_reason' => 'required|string']);

        $agreement->update([
            'status' => 'skip_requested',
            'skip_requested_by_user_id' => Auth::id(),
            'skip_reason' => $request->skip_reason,
        ]);

        return back()->with('success', 'Agreement skip approval request submitted to Founder/Director.');
    }

    public function approveAgreementSkip(Agreement $agreement)
    {
        Gate::authorize('approve-agreement-skips');

        $agreement->update([
            'status' => 'skipped',
            'skip_approved_by_user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Agreement skip APPROVED by Founder/Director.');
    }

    public function recordPayment(Request $request, Booking $booking)
    {
        Gate::authorize('manage-commissions');

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'transaction_reference' => 'nullable|string',
        ]);

        Payment::create([
            'company_id' => Auth::user()->company_id,
            'booking_id' => $booking->id,
            'receipt_number' => 'RCT-' . rand(10000, 99999),
            'amount' => $validated['amount'],
            'payment_date' => now(),
            'payment_method' => $validated['payment_method'],
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'status' => 'cleared',
            'recorded_by_user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Payment recorded successfully!');
    }

    public function destroy(Booking $booking)
    {
        if (!Auth::user()->isCompanyAdmin() && Auth::user()->role?->slug !== 'founder') {
            return back()->with('error', 'Only Company Admins can delete bookings.');
        }

        $bookingCode = $booking->booking_code;
        if ($booking->unit) {
            $booking->unit->update(['status' => 'available']);
        }
        $booking->delete();

        \App\Services\AuditLogService::log('booking_deleted', "Deleted Booking {$bookingCode} and unlocked unit.", null);

        return redirect()->route('bookings.index')->with('success', "Booking {$bookingCode} deleted and unit unlocked successfully.");
    }

    public function show($id)
    {
        if (Auth::user()->isBroker()) {
            return redirect()->route('dashboard');
        }

        $booking = Booking::with(['lead', 'project', 'unit.building', 'salesUser', 'agreement', 'payments.recordedBy'])->findOrFail($id);

        $totalPaid = $booking->payments->where('status', 'cleared')->sum('amount') + $booking->booking_amount;
        $unitPrice = $booking->unit->final_price ?? $booking->unit->price ?? 0;
        $balanceRemaining = max(0, $unitPrice - $totalPaid);

        return view('bookings.show', compact('booking', 'totalPaid', 'unitPrice', 'balanceRemaining'));
    }
}
