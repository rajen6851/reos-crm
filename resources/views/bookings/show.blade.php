@extends('layouts.reos')

@section('title', "Booking {$booking->booking_code} – REOS")

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header Navigation & Action Bar -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5">
                <a href="{{ route('bookings.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600">← Back to Bookings Directory</a>
                <span class="text-slate-300">•</span>
                <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-mono font-bold text-[11px]">{{ $booking->booking_code }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">Property Booking Record</h1>
            <p class="text-xs text-slate-500 mt-0.5">Created on {{ $booking->created_at->format('d M Y, h:i A') }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $booking->status === 'approved' || $booking->status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($booking->status === 'rejected' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
                Status: {{ strtoupper($booking->status) }}
            </span>

            <a href="{{ route('bookings.download-receipt', $booking->id) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shadow-xs flex items-center space-x-1.5">
                <i class="fa-solid fa-file-pdf mr-1"></i><span>Download PDF Receipt</span>
            </a>

            @if($booking->status === 'pending_approval' && (auth()->user()->isCompanyAdmin() || auth()->user()->role?->slug === 'founder' || auth()->user()->isManager()))
            <form method="POST" action="{{ route('bookings.approve', $booking->id) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-xs">
                    <i class="fa-solid fa-check mr-1"></i>Approve Booking
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- 2 Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT 2 COLS: Financial Summary & Payments -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Payment Metrics Strip -->
            <div class="grid grid-cols-3 gap-3">
                <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[11px] font-medium text-slate-500">Unit Final Price</span>
                    <div class="text-lg font-bold font-mono text-slate-900 mt-0.5">₹{{ number_format($unitPrice) }}</div>
                </div>
                <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[11px] font-medium text-emerald-700">Total Paid</span>
                    <div class="text-lg font-bold font-mono text-emerald-700 mt-0.5">₹{{ number_format($totalPaid) }}</div>
                </div>
                <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[11px] font-medium text-amber-700">Balance Remaining</span>
                    <div class="text-lg font-bold font-mono text-amber-700 mt-0.5">₹{{ number_format($balanceRemaining) }}</div>
                </div>
            </div>

            <!-- Payment Ledger & Receipts -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-2xs space-y-4">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900"><i class="fa-solid fa-money-bill-wave text-emerald-600 mr-1"></i>Payment Collection History</h3>
                    @can('manage-commissions')
                    <button onclick="document.getElementById('recordPaymentModal').classList.remove('hidden')" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold rounded-xl transition">
                        + Record Payment
                    </button>
                    @endcan
                </div>

                <div class="space-y-2.5">
                    <!-- Initial Booking Amount -->
                    <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl flex justify-between items-center text-xs">
                        <div>
                            <div class="font-bold text-slate-900">Token Booking Payment</div>
                            <div class="text-[11px] text-slate-500 font-mono">Date: {{ $booking->created_at->format('d M Y') }}</div>
                        </div>
                        <div class="text-right font-mono">
                            <div class="font-bold text-emerald-700 text-sm">₹{{ number_format($booking->booking_amount) }}</div>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">CLEARED</span>
                        </div>
                    </div>

                    @foreach($booking->payments as $payment)
                    <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl flex justify-between items-center text-xs">
                        <div>
                            <div class="font-bold text-slate-900">Receipt {{ $payment->receipt_number }} ({{ strtoupper($payment->payment_method) }})</div>
                            <div class="text-[11px] text-slate-500 font-mono">Ref: {{ $payment->transaction_reference ?? 'N/A' }} • Date: {{ $payment->payment_date?->format('d M Y') }}</div>
                        </div>
                        <div class="text-right font-mono">
                            <div class="font-bold text-emerald-700 text-sm">₹{{ number_format($payment->amount) }}</div>
                            <a href="{{ route('payments.download-receipt', $payment->id) }}" class="text-[10px] text-indigo-600 hover:underline font-bold">Download Receipt ↓</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- RIGHT 1 COL: Customer, Unit & Agreement Details -->
        <div class="space-y-6 text-xs">
            <!-- Customer Details Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-2xs space-y-3">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Customer Info</h3>
                <div class="space-y-2">
                    <div>
                        <div class="text-slate-400 font-medium text-[11px]">Customer Name</div>
                        <div class="font-bold text-slate-900 text-sm">{{ $booking->lead->first_name ?? '' }} {{ $booking->lead->last_name ?? '' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium text-[11px]">Phone Number</div>
                        <div class="font-mono font-bold text-slate-900">{{ $booking->lead->phone ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium text-[11px]">Email Address</div>
                        <div class="font-mono text-slate-700">{{ $booking->lead->email ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Unit Specs Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-2xs space-y-3">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Unit Information</h3>
                <div class="space-y-2">
                    <div>
                        <div class="text-slate-400 font-medium text-[11px]">Project</div>
                        <div class="font-bold text-indigo-600">{{ $booking->project->name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium text-[11px]">Unit Number</div>
                        <div class="font-mono font-bold text-slate-900">Unit {{ $booking->unit->unit_number ?? 'N/A' }} ({{ $booking->unit->unit_type ?? '' }})</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium text-[11px]">Tower / Building</div>
                        <div class="font-bold text-slate-800">{{ $booking->unit->building->name ?? 'Tower 1' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium text-[11px]">Carpet Area</div>
                        <div class="font-mono text-slate-800">{{ $booking->unit->carpet_area ?? 'N/A' }} sqft</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div id="recordPaymentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-md p-6 rounded-2xl space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Record Installment Payment</h3>
                <button onclick="document.getElementById('recordPaymentModal').classList.add('hidden')" class="text-slate-400 font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('bookings.payment', $booking->id) }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Payment Amount (₹) *</label>
                    <input type="number" name="amount" required value="500000" min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Payment Method *</label>
                    <select name="payment_method" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500">
                        <option value="bank_transfer">Bank Transfer / NEFT</option>
                        <option value="cheque">Cheque</option>
                        <option value="upi">UPI / Online</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Transaction Reference / Cheque No.</label>
                    <input type="text" name="transaction_reference" placeholder="TXN987654321" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('recordPaymentModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
