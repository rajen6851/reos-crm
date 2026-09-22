@extends('layouts.reos')

@section('title', 'Bookings & Contracts â€“ UrbanProperty')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ searchQuery: '' }">
    <!-- Header Banner -->
    <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#2563EB] transition">Home</a>
                <span>&gt;</span>
                <span class="text-slate-900 font-bold">Deals & Bookings</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Bookings & Property Unit Locks</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Manage customer unit bookings, agreement status, payment schedules, and sales approvals</p>
        </div>

        @can('approve-bookings')
        <div>
            <button onclick="document.getElementById('createBookingModal').classList.remove('hidden')" class="px-4 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>New Booking Entry</span>
            </button>
        </div>
        @endcan
    </div>

    <!-- Pending Critical Approval Requests for Director / Founder / Main Owner -->
    @if(auth()->user()->isDirectorOrFounder() && isset($pendingBookingApprovals) && $pendingBookingApprovals->count() > 0)
    <div class="bg-amber-50/70 border border-amber-200 rounded-3xl p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 border border-amber-300 text-amber-700 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-[#0F172A] text-sm">Critical Approval Requests (Main Owner Verification Required)</h3>
                    <p class="text-xs text-[#64748B]">Admins have requested booking cancellation/deletion actions that require your Director/Owner authorization before execution.</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-amber-200/80 text-amber-900 text-xs font-bold rounded-full border border-amber-300">
                {{ $pendingBookingApprovals->count() }} Pending Request{{ $pendingBookingApprovals->count() > 1 ? 's' : '' }}
            </span>
        </div>

        <div class="space-y-3">
            @foreach($pendingBookingApprovals as $approval)
            <div class="bg-white rounded-2xl p-4 border border-amber-200/90 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start space-x-3">
                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg border {{ $approval->action_badge }}">
                        {{ $approval->action_label }}
                    </span>
                    <div>
                        <div class="text-xs font-bold text-[#0F172A]">
                            Target Booking: <span class="text-[#DC2626] font-mono">{{ $approval->target_name }}</span>
                        </div>
                        <div class="text-[11px] text-[#64748B] mt-0.5">
                            Requested by Admin: <strong class="text-slate-800">{{ $approval->requestedBy->name ?? 'Admin User' }}</strong>
                            â€¢ <span class="font-mono">{{ $approval->created_at->diffForHumans() }}</span>
                        </div>
                        @if($approval->reason)
                            <div class="text-[11px] text-amber-900 bg-amber-50 rounded-lg p-2 mt-2 border border-amber-200">
                                ðŸ’¬ <em>"{{ $approval->reason }}"</em>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center space-x-2 shrink-0 self-end md:self-center">
                    <form action="{{ route('users.approvals.approve', $approval->id) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to APPROVE & DELETE this booking contract and unlock unit?')" class="px-4 py-2 bg-[#059669] hover:bg-[#047857] text-white text-xs font-bold rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                            <i class="fa-solid fa-check text-xs"></i>
                            <span>Approve & Delete Booking</span>
                        </button>
                    </form>

                    <form action="{{ route('users.approvals.reject', $approval->id) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to REJECT this cancellation request?')" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                            <i class="fa-solid fa-xmark text-xs"></i>
                            <span>Reject</span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Summary Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Total Unit Bookings</span>
                <div class="text-2xl font-extrabold text-[#0F172A] mt-1 font-mono">{{ $bookings->total() }} Bookings</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-[#4F46E5] flex items-center justify-center text-lg border border-indigo-100"><i class="fa-solid fa-receipt text-[#4F46E5]"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Approved & Confirmed</span>
                <div class="text-2xl font-extrabold text-[#059669] mt-1 font-mono">{{ $bookings->filter(fn($b) => $b->approval_status === 'approved' || $b->status === 'approved' || $b->status === 'confirmed')->count() }} Confirmed</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#059669] flex items-center justify-center text-lg border border-emerald-200"><i class="fa-solid fa-circle-check text-[#059669]"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Pending Approvals</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-1 font-mono">{{ $bookings->filter(fn($b) => $b->approval_status === 'pending')->count() }} Pending</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg border border-amber-200"><i class="fa-solid fa-clock text-amber-600"></i></span>
        </div>
    </div>

    <!-- Bookings Table Directory Card -->
    <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-2xs p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-[#0F172A]">Current Bookings & Agreement Directory</h2>

            <!-- Search Filter -->
            <div class="relative min-w-[240px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Search Customer Name..." class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl pl-9 pr-3 py-2 text-xs font-semibold text-[#0F172A] focus:outline-none focus:border-[#4F46E5]">
            </div>
        </div>
        
        @if($bookings->isEmpty())
            <div class="p-8 text-center text-[#64748B] font-medium text-xs rounded-2xl bg-slate-50 border border-dashed border-[#CBD5E1]">No bookings recorded yet. Click "+ New Booking Entry" to initiate unit lock.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[#475569] font-extrabold uppercase border-b border-[#E2E8F0]">
                        <tr>
                            <th class="p-3.5">Booking Code</th>
                            <th class="p-3.5">Customer Details</th>
                            <th class="p-3.5">Unit & Project</th>
                            <th class="p-3.5">Total Unit Cost</th>
                            <th class="p-3.5">Approval Status</th>
                            <th class="p-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($bookings as $booking)
                        @php
                            $unitCost = ($booking->total_unit_cost > 0)
                                ? $booking->total_unit_cost 
                                : ($booking->unit->final_price ?? $booking->unit->base_price ?? $booking->booking_amount ?? 0);
                        @endphp
                        <tr x-show="searchQuery === '' || '{{ strtolower($booking->customer_name) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 font-mono font-extrabold text-[#4F46E5]">
                                <a href="{{ route('bookings.show', $booking->id) }}" class="hover:underline">
                                    {{ $booking->booking_code }}
                                </a>
                            </td>
                            <td class="p-3.5">
                                <div class="font-bold text-[#0F172A]">{{ $booking->customer_name }}</div>
                                <div class="text-[11px] text-[#64748B] font-mono">{{ $booking->customer_email ?? $booking->customer_phone }}</div>
                            </td>
                            <td class="p-3.5">
                                <div class="font-bold text-[#0F172A]">Unit {{ $booking->unit->unit_number ?? 'N/A' }}</div>
                                <div class="text-[11px] text-[#64748B]"><i class="fa-solid fa-building text-[#4F46E5] mr-1"></i>{{ $booking->project->name ?? '' }}</div>
                            </td>
                            <td class="p-3.5 font-mono font-extrabold text-[#059669]">
                                â‚¹{{ number_format($unitCost) }}
                            </td>
                            <td class="p-3.5">
                                @if($booking->approval_status === 'approved' || $booking->status === 'approved' || $booking->status === 'confirmed')
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-[#059669] border border-emerald-200 uppercase">Approved</span>
                                @elseif($booking->approval_status === 'rejected')
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-rose-50 text-[#DC2626] border border-rose-200 uppercase">Rejected</span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-50 text-amber-800 border border-amber-200 uppercase">Pending Approval</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('bookings.show', $booking->id) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-[#4F46E5] text-xs font-bold rounded-xl border border-indigo-200 transition flex items-center space-x-1">
                                        <i class="fa-solid fa-file-signature text-[#4F46E5] mr-1"></i>View Record
                                    </a>

                                    <a href="{{ route('bookings.download-receipt', $booking->id) }}" class="p-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200 text-xs transition" title="Download PDF Receipt">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>

                                    @if(auth()->user()->isCompanyAdmin() || auth()->user()->role?->slug === 'founder')
                                    <form method="POST" action="{{ route('bookings.destroy', $booking->id) }}" onsubmit="return confirm('Delete booking {{ $booking->booking_code }}?');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-[#64748B] hover:text-[#DC2626] transition cursor-pointer" title="Delete Booking">
                                            <i class="fa-solid fa-trash-can text-[#DC2626]"></i>
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
        <div class="bg-white w-full max-w-lg p-6 rounded-3xl space-y-4 border border-[#E2E8F0] shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-[#0F172A]">Initiate Unit Booking Lock</h3>
                <button onclick="document.getElementById('createBookingModal').classList.add('hidden')" class="text-slate-400 font-bold">âœ•</button>
            </div>

            <form method="POST" action="{{ route('bookings.store') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[#475569] mb-1 font-bold">Customer Lead *</label>
                    <select name="lead_id" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-bold focus:outline-none focus:border-[#4F46E5]">
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->first_name }} {{ $lead->last_name }} ({{ $lead->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[#475569] mb-1 font-bold">Unit Lock Selection *</label>
                    <select name="unit_id" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-bold focus:outline-none focus:border-[#4F46E5]">
                        @foreach($availableUnits as $unit)
                            <option value="{{ $unit->id }}">Unit {{ $unit->unit_number }} ({{ $unit->unit_type }}) - â‚¹{{ number_format($unit->price ?? $unit->final_price ?? 7800000) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Token Advance Amount (â‚¹) *</label>
                        <input type="number" name="booking_amount" required value="100000" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Total Agreement Cost (â‚¹) *</label>
                        <input type="number" name="agreement_value" required value="7500000" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                </div>

                <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200 flex items-center space-x-2">
                    <input type="checkbox" name="is_agreement_skipped" id="skip_agree" value="1" class="rounded text-[#4F46E5]">
                    <label for="skip_agree" class="text-xs font-bold text-amber-900 cursor-pointer">Skip Agreement Draft Stage (Direct Express Unit Lock)</label>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('createBookingModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-[#0F172A] font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#4F46E5] hover:bg-[#4338CA] text-white font-bold rounded-xl shadow-xs">Lock Unit & Create Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
