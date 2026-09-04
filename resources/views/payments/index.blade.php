@extends('layouts.reos')

@section('title', 'Payments & GST Tax Receipts - REOS')

@section('content')
<div class="space-y-6">
    <div class="reos-card p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Payments & Invoices</span>
            </div>
            <h1 class="page-heading text-2xl">Payments Ledger & GST Tax Receipts</h1>
            <p class="body-text text-xs mt-0.5">Recorded unit token payments, Razorpay gateways, and official tax invoice downloads</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-[#059669] bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-xl shadow-2xs">
            <span>Total Collected: <strong class="font-mono text-sm">₹{{ number_format($totalCollected) }}</strong></span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Revenue Collected</div>
            <div class="text-3xl font-black text-emerald-600 font-mono mt-2">₹{{ number_format($totalCollected) }}</div>
            <div class="text-xs text-slate-600 font-bold mt-1">Confirmed Token Payments</div>
        </div>
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Razorpay India Gateway</div>
            <div class="text-3xl font-black text-indigo-600 font-mono mt-2">{{ $razorpayPaymentsCount }}</div>
            <div class="text-xs text-indigo-700 font-bold mt-1">Online Direct Receipts</div>
        </div>
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Manual / Cheque Payments</div>
            <div class="text-3xl font-black text-purple-600 font-mono mt-2">{{ $manualPaymentsCount }}</div>
            <div class="text-xs text-purple-700 font-bold mt-1">Bank Transfers & Cash</div>
        </div>
    </div>

    <!-- Recorded Payments Ledger Table -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-lg font-black text-slate-900">Recorded Unit Token Payments</h2>
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-800 font-extrabold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-4">Receipt / Customer</th>
                        <th class="p-4">Project & Unit</th>
                        <th class="p-4">Amount Paid</th>
                        <th class="p-4">Payment Method</th>
                        <th class="p-4">Payment Date</th>
                        <th class="p-4 text-right">Tax Invoice PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-900">
                            <div>{{ $p->booking->customer_name ?? 'Customer' }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $p->receipt_number }}</div>
                        </td>
                        <td class="p-4 text-xs font-bold text-slate-900">
                            <div><i class="fa-solid fa-building text-indigo-600 mr-1"></i>{{ $p->booking->project->name ?? 'N/A' }}</div>
                            <span class="text-[10px] text-slate-500 font-mono">Unit {{ $p->booking->unit->unit_number ?? 'N/A' }}</span>
                        </td>
                        <td class="p-4 font-mono font-bold text-emerald-600 text-sm">
                            ₹{{ number_format($p->amount) }}
                        </td>
                        <td class="p-4 text-xs">
                            <span class="px-3 py-1 font-bold rounded-full bg-emerald-100 text-emerald-900 border border-emerald-300">
                                <i class="fa-solid fa-bolt text-emerald-600 mr-1"></i>{{ strtoupper($p->payment_method ?? 'Razorpay') }}
                            </span>
                        </td>
                        <td class="p-4 text-xs text-slate-600 font-mono">
                            {{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('d M Y, h:i A') : 'N/A' }}
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('payments.download-receipt', $p->id) }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white font-bold text-xs border border-indigo-200 shadow-2xs transition inline-flex items-center space-x-1">
                                <i class="fa-solid fa-file-pdf mr-1"></i>
                                <span>Download PDF</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    @forelse($bookings as $b)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-900">
                            <div>{{ $b->customer_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $b->booking_code }}</div>
                        </td>
                        <td class="p-4 text-xs font-bold text-slate-900">
                            <div><i class="fa-solid fa-building text-indigo-600 mr-1"></i>{{ $b->project->name ?? 'N/A' }}</div>
                            <span class="text-[10px] text-slate-500 font-mono">Unit {{ $b->unit->unit_number ?? 'N/A' }}</span>
                        </td>
                        <td class="p-4 font-mono font-bold text-emerald-600 text-sm">
                            ₹{{ number_format($b->booking_amount) }}
                        </td>
                        <td class="p-4 text-xs">
                            <span class="px-3 py-1 font-bold rounded-full bg-emerald-100 text-emerald-900 border border-emerald-300">
                                <i class="fa-solid fa-bolt text-emerald-600 mr-1"></i>RAZORPAY INDIA ACTIVE
                            </span>
                        </td>
                        <td class="p-4 text-xs text-slate-600 font-mono">
                            {{ $b->booking_date ? \Carbon\Carbon::parse($b->booking_date)->format('d M Y, h:i A') : 'N/A' }}
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('bookings.download-receipt', $b->id) }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white font-bold text-xs border border-indigo-200 shadow-2xs transition inline-flex items-center space-x-1">
                                <i class="fa-solid fa-file-pdf mr-1"></i>
                                <span>Download PDF</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500 font-medium text-xs">No completed payments recorded yet.</td>
                    </tr>
                    @endforelse
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
