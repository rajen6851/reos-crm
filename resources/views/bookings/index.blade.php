@extends('layouts.reos')

@section('title', 'Bookings & Agreements - REOS')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="reos-card p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="page-heading">Bookings & Unit Locks</h1>
            <p class="body-text text-xs mt-0.5">Manage customer unit bookings, agreement status, and payment collections.</p>
        </div>
        <div>
            <button onclick="document.getElementById('createBookingModal').classList.remove('hidden')" class="px-5 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text rounded-xl transition shadow-xs flex items-center space-x-1.5 cursor-pointer">
                <span>+ New Booking Entry</span>
            </button>
        </div>
    </div>

    <!-- Bookings Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-900">Current Bookings & Agreement Status</h2>
        
        @if($bookings->isEmpty())
            <div class="p-6 text-center text-slate-500 font-medium text-xs rounded-2xl bg-slate-50 border border-slate-200">No bookings recorded yet. Click "+ New Booking Entry" to initiate unit lock.</div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50/80 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Booking Code</th>
                            <th class="py-3 px-4">Customer Name</th>
                            <th class="py-3 px-4">Unit & Project</th>
                            <th class="py-3 px-4">Total Cost</th>
                            <th class="py-3 px-4">Approval Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($bookings as $booking)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-700">
                                <a href="{{ route('bookings.show', $booking->id) }}" class="hover:underline">
                                    {{ $booking->booking_code }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $booking->customer_name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $booking->customer_email }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                Unit {{ $booking->unit->unit_number ?? 'N/A' }}
                                <div class="text-[10px] text-slate-400 font-normal">{{ $booking->project->name ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">₹{{ number_format($booking->agreement_value ?? 0) }}</td>
                            <td class="py-3.5 px-4">
                                @if($booking->approval_status === 'approved')
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Approved</span>
                                @elseif($booking->approval_status === 'rejected')
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200">Rejected</span>
                                @else
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-amber-50 text-amber-800 border border-amber-200">Pending</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('bookings.show', $booking->id) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl border border-indigo-200 transition">
                                        <i class="fa-solid fa-file-signature text-indigo-600 mr-1"></i>View Booking Record
                                    </a>

                                    <a href="{{ route('bookings.download-receipt', $booking->id) }}" class="p-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200 text-xs transition" title="Download PDF Receipt">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>

                                    @if(auth()->user()->isCompanyAdmin() || auth()->user()->role?->slug === 'founder')
                                    <form method="POST" action="{{ route('bookings.destroy', $booking->id) }}" onsubmit="return confirm('Delete booking {{ $booking->booking_code }}?');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition" title="Delete Booking">
                                            <i class="fa-solid fa-trash-can text-rose-500"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

    <!-- Create Booking Entry Modal -->
    <div id="createBookingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-lg p-6 rounded-2xl space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Initiate Unit Booking Lock</h3>
                <button onclick="document.getElementById('createBookingModal').classList.add('hidden')" class="text-slate-400 font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('bookings.store') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Customer Lead *</label>
                    <select name="lead_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500">
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->first_name }} {{ $lead->last_name }} ({{ $lead->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Unit Lock Selection *</label>
                    <select name="unit_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500">
                        @foreach($availableUnits as $unit)
                            <option value="{{ $unit->id }}">Unit {{ $unit->unit_number }} ({{ $unit->unit_type }}) - ₹{{ number_format($unit->price ?? $unit->final_price ?? 7800000) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Token Advance Amount (₹) *</label>
                        <input type="number" name="booking_amount" required value="100000" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Total Agreement Cost (₹) *</label>
                        <input type="number" name="agreement_value" required value="7500000" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 flex items-center space-x-2">
                    <input type="checkbox" name="is_agreement_skipped" id="skip_agree" value="1" class="rounded text-indigo-600 focus:ring-indigo-500">
                    <label for="skip_agree" class="text-xs font-semibold text-amber-900 cursor-pointer">Skip Agreement Draft Stage (Direct Express Unit Lock)</label>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('createBookingModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs">Lock Unit & Create Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
