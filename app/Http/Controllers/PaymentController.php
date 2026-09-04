<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['booking.lead', 'booking.unit.project', 'booking.project'])
            ->latest('payment_date')
            ->get();

        $bookings = Booking::with(['lead', 'unit.project', 'project', 'paymentSchedules'])
            ->latest('booking_date')
            ->get();

        $totalCollected = Payment::sum('amount');
        if ($totalCollected == 0) {
            $totalCollected = Booking::sum('booking_amount');
        }

        $razorpayPaymentsCount = Payment::where('payment_method', 'razorpay')->count();
        $manualPaymentsCount = Payment::where('payment_method', '!=', 'razorpay')->count();

        return view('payments.index', compact('payments', 'bookings', 'totalCollected', 'razorpayPaymentsCount', 'manualPaymentsCount'));
    }

    public function generateSchedules(Request $request, $bookingId)
    {
        $booking = Booking::with('unit')->findOrFail($bookingId);
        $user = Auth::user();

        // Standard Real Estate Construction Milestones
        $milestones = [
            ['name' => 'Booking Token Deposit', 'percentage' => 10.00, 'due_days' => 0],
            ['name' => 'Plinth & Foundation Stage', 'percentage' => 20.00, 'due_days' => 30],
            ['name' => 'Structure & Slab Casting', 'percentage' => 30.00, 'due_days' => 90],
            ['name' => 'Internal Finishing & Plaster', 'percentage' => 20.00, 'due_days' => 150],
            ['name' => 'Handover & Possession', 'percentage' => 20.00, 'due_days' => 210],
        ];

        $totalPrice = $booking->agreement_value ?? $booking->agreed_amount ?? $booking->total_amount ?? ($booking->unit->total_price ?? 5000000);

        foreach ($milestones as $m) {
            $dueAmount = ($totalPrice * $m['percentage']) / 100;
            $dueDate = now()->addDays($m['due_days']);

            PaymentSchedule::firstOrCreate(
                [
                    'company_id' => $user->company_id,
                    'booking_id' => $booking->id,
                    'milestone_name' => $m['name'],
                ],
                [
                    'percentage' => $m['percentage'],
                    'due_amount' => $dueAmount,
                    'paid_amount' => ($m['due_days'] === 0) ? min($booking->booking_amount, $dueAmount) : 0,
                    'due_date' => $dueDate,
                    'status' => ($m['due_days'] === 0 && $booking->booking_amount >= $dueAmount) ? 'paid' : 'pending',
                ]
            );
        }

        return back()->with('status', 'Payment schedule milestones generated successfully!');
    }

    public function viewDemandLetter($scheduleId)
    {
        $schedule = PaymentSchedule::with(['booking.lead', 'booking.unit.project', 'booking.company', 'booking.coApplicants'])->findOrFail($scheduleId);
        $schedule->demand_letter_sent_at = now();
        $schedule->save();

        return view('payments.demand_letter', compact('schedule'));
    }

    public function downloadReceipt($id)
    {
        $payment = Payment::with(['booking.lead', 'booking.unit.project', 'booking.project', 'booking.company'])->findOrFail($id);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payments.pdf_receipt', compact('payment'));
            return $pdf->download('Receipt-' . ($payment->receipt_number ?? $payment->id) . '.pdf');
        }

        $dompdf = new \Dompdf\Dompdf();
        $html = view('payments.pdf_receipt', compact('payment'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Receipt-' . ($payment->receipt_number ?? $payment->id) . '.pdf"',
        ]);
    }

    public function downloadBookingReceipt($id)
    {
        $booking = Booking::with(['lead', 'unit.project', 'project', 'company'])->findOrFail($id);

        $payment = (object) [
            'id' => $booking->id,
            'receipt_number' => 'RCT-' . strtoupper(substr(md5($booking->id), 0, 8)),
            'amount' => $booking->booking_amount,
            'payment_method' => 'Razorpay / Online',
            'payment_date' => $booking->booking_date ?? now(),
            'booking' => $booking
        ];

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payments.pdf_receipt', compact('payment'));
            return $pdf->download('Receipt-' . $payment->receipt_number . '.pdf');
        }

        $dompdf = new \Dompdf\Dompdf();
        $html = view('payments.pdf_receipt', compact('payment'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Receipt-' . $payment->receipt_number . '.pdf"',
        ]);
    }
}
