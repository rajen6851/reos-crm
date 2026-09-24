@extends('layouts.reos')

@section('title', 'Brokers Directory - UrbanProperty')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Header Banner -->
    <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#2563EB] transition">Home</a>
                <span>&gt;</span>
                <span class="text-slate-900 font-bold">Brokers Directory</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Brokers Directory</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Manage external broker agencies, commission agreements, and track submitted lead conversions</p>
        </div>

        <div class="flex items-center space-x-3">
            <!-- View Mode Switcher -->
            <div class="flex items-center bg-slate-100 p-1 rounded-lg border border-slate-200">
                <button type="button" id="btnCardsView" onclick="switchBrokerView('cards')" class="px-3 py-1.5 rounded-md text-xs font-bold bg-white text-slate-900 shadow-2xs transition flex items-center space-x-1 cursor-pointer">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>Cards</span>
                </button>
                <button type="button" id="btnTableView" onclick="switchBrokerView('table')" class="px-3 py-1.5 rounded-md text-xs font-semibold text-slate-500 hover:text-slate-900 transition flex items-center space-x-1 cursor-pointer">
                    <i class="fa-solid fa-list"></i>
                    <span>Table</span>
                </button>
            </div>

            <button type="button" onclick="document.getElementById('addBrokerModal').classList.remove('hidden')" class="px-4 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Register Partner Broker</span>
            </button>
        </div>
    </div>

    <!-- Pending Critical Approval Requests for Director / Founder / Main Owner -->
    @if(auth()->user()->isDirectorOrFounder() && isset($pendingBrokerApprovals) && $pendingBrokerApprovals->count() > 0)
    <div class="bg-amber-50/70 border border-amber-200 rounded-3xl p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 border border-amber-300 text-amber-700 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-[#0F172A] text-sm">Critical Approval Requests (Main Owner Verification Required)</h3>
                    <p class="text-xs text-[#64748B]">Admins have requested broker profile deletion actions that require your Director/Owner authorization before execution.</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-amber-200/80 text-amber-900 text-xs font-bold rounded-full border border-amber-300">
                {{ $pendingBrokerApprovals->count() }} Pending Request{{ $pendingBrokerApprovals->count() > 1 ? 's' : '' }}
            </span>
        </div>

        <div class="space-y-3">
            @foreach($pendingBrokerApprovals as $approval)
            <div class="bg-white rounded-2xl p-4 border border-amber-200/90 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start space-x-3">
                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg border {{ $approval->action_badge }}">
                        {{ $approval->action_label }}
                    </span>
                    <div>
                        <div class="text-xs font-bold text-[#0F172A]">
                            Target Broker: <span class="text-[#DC2626] font-mono">{{ $approval->target_name }}</span>
                        </div>
                        <div class="text-[11px] text-[#64748B] mt-0.5">
                            Requested by Admin: <strong class="text-slate-800">{{ $approval->requestedBy->name ?? 'Admin User' }}</strong>
                            • <span class="font-mono">{{ $approval->created_at->diffForHumans() }}</span>
                        </div>
                        @if($approval->reason)
                            <div class="text-[11px] text-amber-900 bg-amber-50 rounded-lg p-2 mt-2 border border-amber-200">
                                💬 <em>"{{ $approval->reason }}"</em>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center space-x-2 shrink-0 self-end md:self-center">
                    <form action="{{ route('users.approvals.approve', $approval->id) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to APPROVE & DELETE this broker profile?')" class="px-4 py-2 bg-[#059669] hover:bg-[#047857] text-white text-xs font-bold rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                            <i class="fa-solid fa-check text-xs"></i>
                            <span>Approve & Delete Broker</span>
                        </button>
                    </form>

                    <form action="{{ route('users.approvals.reject', $approval->id) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to REJECT this deletion request?')" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
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

    <!-- CARDS GRID VIEW (Exact Match to Reference Screenshot Design) -->
    <div id="cardsViewContainer" class="block">
        @if($brokers->isEmpty())
            <div class="p-8 text-center bg-white rounded-3xl border border-[#E2E8F0] text-xs text-slate-500 font-medium">
                No channel partner brokers registered yet. Click "+ Register Partner Broker" to onboard your first agency.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($brokers as $b)
                @php
                    $initials = strtoupper(substr($b->agency_name, 0, 2));
                    $contactName = $b->user->name ?? $b->agency_name;
                    $statusBadge = ($b->status === 'active')
                        ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                        : 'bg-slate-50 text-slate-700 border-slate-200';
                @endphp
                <div class="bg-white rounded-3xl p-6 border border-[#E2E8F0] shadow-2xs hover:shadow-md transition space-y-4 relative">
                    <!-- Card Header: Avatar + Agency Name + Broker Code + Status + Red 3-Dots Menu -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs shrink-0">
                                {{ $initials }}
                            </div>
                            <div>
                                <div class="text-slate-900 font-extrabold text-base leading-tight">{{ $b->agency_name }}</div>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-[#4F46E5] font-mono font-bold text-[11px] border border-indigo-200">
                                        {{ $b->broker_code }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $statusBadge }}">
                                        {{ $b->status ?? 'active' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Red 3-Dots Vertical Dropdown Menu -->
                        <div class="relative">
                            <button type="button" onclick="event.stopPropagation(); toggleBrokerDropdown({{ $b->id }});" class="w-8 h-9 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white flex items-center justify-center transition shadow-md cursor-pointer active:scale-95" title="More Options">
                                <i class="fa-solid fa-ellipsis-vertical text-base pointer-events-none"></i>
                            </button>

                            <div id="brokerDropdownMenu_{{ $b->id }}" class="hidden absolute right-0 mt-2 w-36 bg-white rounded-2xl shadow-2xl border border-slate-200 p-2 z-50 text-xs space-y-1">
                                <!-- Option 1: Edit -->
                                <button type="button" onclick="event.stopPropagation(); openEditBrokerModal({{ json_encode($b) }}); hideAllBrokerDropdowns();" class="w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-100 rounded-xl font-bold flex items-center space-x-2 transition cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square text-slate-500 text-xs"></i>
                                    <span>Edit Specs</span>
                                </button>

                                <!-- Option 2: Delete -->
                                @if(auth()->user()->isCompanyAdmin() || auth()->user()->role?->slug === 'founder')
                                <form method="POST" action="{{ route('brokers.destroy', $b->id) }}" onsubmit="return confirm('Delete partner agency {{ $b->agency_name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-left px-3 py-2 text-rose-600 hover:bg-rose-50 rounded-xl font-bold flex items-center space-x-2 transition cursor-pointer">
                                        <i class="fa-solid fa-trash-can text-rose-500 text-xs"></i>
                                        <span>Delete</span>
                                    </button>
                                </form>
                                @endif

                                <!-- Option 3: Preview -->
                                <a href="{{ route('brokers.show', $b->id) }}" class="w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-100 rounded-xl font-bold flex items-center space-x-2 transition block">
                                    <i class="fa-solid fa-eye text-slate-500 text-xs"></i>
                                    <span>View Ledger</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Complete Contact Details -->
                    <div class="bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/80 space-y-2 text-xs text-[#0F172A]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#64748B] text-[11px] font-semibold uppercase">Contact Person</span>
                            <span class="font-bold text-slate-900 flex items-center space-x-1">
                                <i class="fa-solid fa-user-tie text-emerald-600 text-xs"></i>
                                <span>{{ $contactName }}</span>
                            </span>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-200/60 pt-1.5">
                            <span class="text-[#64748B] text-[11px] font-semibold uppercase">Phone</span>
                            <span class="font-mono font-bold text-slate-900">{{ $b->phone }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-200/60 pt-1.5">
                            <span class="text-[#64748B] text-[11px] font-semibold uppercase">Email</span>
                            <span class="font-mono font-bold text-slate-900">{{ $b->email }}</span>
                        </div>
                    </div>

                    <!-- Performance Metrics Strip: Submitted Leads, Converted Bookings, Commission Rate -->
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="p-2.5 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                            <span class="text-[10px] text-[#64748B] font-semibold uppercase block">Leads</span>
                            <span class="font-mono font-extrabold text-[#4F46E5] text-sm mt-0.5 block">{{ $b->total_submitted_leads ?? 0 }}</span>
                        </div>

                        <div class="p-2.5 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                            <span class="text-[10px] text-[#64748B] font-semibold uppercase block">Booked</span>
                            <span class="font-mono font-extrabold text-[#059669] text-sm mt-0.5 block">{{ $b->converted_leads ?? 0 }}</span>
                        </div>

                        <div class="p-2.5 rounded-2xl bg-purple-50/60 border border-purple-100">
                            <span class="text-[10px] text-[#64748B] font-semibold uppercase block">Comm.</span>
                            <span class="font-mono font-extrabold text-purple-700 text-sm mt-0.5 block">{{ number_format($b->commission_rate, 2) }}%</span>
                        </div>
                    </div>

                    <!-- Footer Action Strip: Direct Communication & Profile Link -->
                    <div class="border-t border-[#E2E8F0] pt-3 flex items-center justify-between gap-2">
                        <div class="flex items-center space-x-2">
                            <a href="mailto:{{ $b->email }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-indigo-600 transition" title="Send Email">
                                <i class="fa-regular fa-envelope text-xs"></i>
                            </a>
                            <a href="tel:{{ $b->phone }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 transition" title="Call Phone">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </a>
                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $b->phone) }}" target="_blank" class="px-2.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#059669] font-bold text-xs border border-emerald-200 transition flex items-center space-x-1" title="WhatsApp Chat">
                                <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                <span class="hidden sm:inline">WhatsApp</span>
                            </a>
                        </div>

                        <a href="{{ route('brokers.show', $b->id) }}" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-[#4F46E5] font-bold text-xs rounded-xl border border-indigo-200 transition flex items-center space-x-1 shrink-0">
                            <span>Ledger & Profile</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- TABLE VIEW (Alternative List Layout) -->
    <div id="tableViewContainer" class="hidden bg-white rounded-3xl border border-[#E2E8F0] shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] flex justify-between items-center">
            <h3 class="section-heading text-base">Registered Channel Partners List</h3>
            <span class="text-xs text-[#64748B] font-medium">Total {{ $brokers->count() }} Agencies</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-[#64748B] font-bold border-b border-[#E2E8F0] uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="p-4">Broker Code & Agency</th>
                        <th class="p-4">Contact Person</th>
                        <th class="p-4">Phone / Email</th>
                        <th class="p-4">Commission Rate</th>
                        <th class="p-4">Submitted Leads</th>
                        <th class="p-4">Converted</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] font-medium">
                    @forelse($brokers as $b)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-4 font-bold text-slate-900">
                            <a href="{{ route('brokers.show', $b->id) }}" class="hover:underline flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-full bg-indigo-50 text-[#4F46E5] font-extrabold text-xs flex items-center justify-center border border-indigo-200 shrink-0">
                                    {{ strtoupper(substr($b->agency_name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="text-[#0F172A] font-extrabold text-sm">{{ $b->agency_name }}</div>
                                    <div class="text-[10px] text-[#64748B] font-mono font-normal">{{ $b->broker_code }}</div>
                                </div>
                            </a>
                        </td>
                        <td class="p-4 text-xs font-semibold text-[#0F172A]">
                            {{ $b->user->name ?? $b->agency_name }}
                        </td>
                        <td class="p-4 text-xs font-mono">
                            <div class="font-bold text-[#0F172A]">{{ $b->phone }}</div>
                            <div class="text-[#64748B]">{{ $b->email }}</div>
                        </td>
                        <td class="p-4 font-mono font-extrabold text-purple-700">
                            {{ $b->commission_rate }}%
                        </td>
                        <td class="p-4 font-mono font-bold text-[#0F172A]">
                            {{ $b->total_submitted_leads }} Leads
                        </td>
                        <td class="p-4 font-mono font-bold text-[#059669]">
                            {{ $b->converted_leads }} Booked
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button type="button" onclick="openEditBrokerModal({{ json_encode($b) }})" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 rounded-xl text-xs font-bold transition flex items-center space-x-1 cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square text-amber-700"></i>
                                    <span>Edit</span>
                                </button>

                                <a href="{{ route('brokers.show', $b->id) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-[#4F46E5] btn-text rounded-xl border border-indigo-200 transition">
                                    Preview
                                </a>

                                @if(auth()->user()->isCompanyAdmin() || auth()->user()->role?->slug === 'founder')
                                <form method="POST" action="{{ route('brokers.destroy', $b->id) }}" onsubmit="return confirm('Delete broker {{ $b->agency_name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-[#DC2626] transition" title="Delete Broker">
                                        <i class="fa-solid fa-trash-can text-rose-500"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-[#64748B] font-medium text-xs">No channel partner brokers registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- RIGHT SLIDE-OVER DRAWER PANEL 1: Register Partner Broker -->
    <div id="addBrokerModal" class="hidden fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop Blur -->
        <div onclick="document.getElementById('addBrokerModal').classList.add('hidden')" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl z-50 flex flex-col justify-between transform transition-transform duration-300 ease-in-out border-l border-[#E2E8F0]">
            <!-- Header -->
            <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-200 text-[#DC2626] flex items-center justify-center font-extrabold text-sm shrink-0">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                    <div>
                        <h3 class="section-heading text-lg">Register Partner Broker</h3>
                        <p class="body-text text-xs text-[#64748B]">Add channel partner agency credentials</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('addBrokerModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center font-bold text-sm transition cursor-pointer">✕</button>
            </div>

            <!-- Drawer Form Body (Scrollable) -->
            <form id="addBrokerForm" method="POST" action="{{ route('brokers.store') }}" class="p-6 overflow-y-auto flex-1 space-y-5 text-xs">
                @csrf

                <!-- Section 1: Agency Specs -->
                <div class="space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#4F46E5] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-building text-xs"></i>
                        <span>1. Agency Information</span>
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Agency Name *</label>
                        <input type="text" name="agency_name" required placeholder="e.g. Shree Ram Realty Services" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-extrabold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Contact Person Name *</label>
                        <input type="text" name="contact_name" required placeholder="e.g. Rajesh Verma" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                    </div>
                </div>

                <!-- Section 2: Contact & Commission -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#0F172A] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-percent text-xs text-slate-500"></i>
                        <span>2. Contact & Terms</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Email Address *</label>
                            <input type="email" name="email" required placeholder="broker@agency.com" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5]">
                        </div>
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Phone Number *</label>
                            <input type="tel" name="phone" required placeholder="9876543210" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5]">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Commission Rate (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="commission_rate" value="2.50" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                        </div>
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Login Password *</label>
                            <input type="text" name="password" required placeholder="Enter password" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] focus:outline-none focus:border-[#4F46E5]">
                        </div>
                    </div>
                </div>
            </form>

            <!-- Footer Actions -->
            <div class="p-6 border-t border-[#E2E8F0] bg-slate-50/80 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('addBrokerModal').classList.add('hidden')" class="px-5 py-2.5 bg-white border border-[#E2E8F0] text-[#0F172A] btn-text rounded-xl hover:bg-slate-50 transition cursor-pointer">Cancel</button>
                <button type="submit" form="addBrokerForm" class="px-6 py-2.5 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text rounded-xl shadow-xs transition cursor-pointer">Register Broker</button>
            </div>
        </div>
    </div>

    <!-- RIGHT SLIDE-OVER DRAWER PANEL 2: Edit Partner Broker Specs -->
    <div id="editBrokerModal" class="hidden fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop Blur -->
        <div onclick="document.getElementById('editBrokerModal').classList.add('hidden')" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl z-50 flex flex-col justify-between transform transition-transform duration-300 ease-in-out border-l border-[#E2E8F0]">
            <!-- Header -->
            <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-200 text-[#4F46E5] flex items-center justify-center font-extrabold text-sm shrink-0">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="section-heading text-lg">Edit Partner Broker Specs</h3>
                        <p class="body-text text-xs text-[#64748B]">Update agency profile & commission terms</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('editBrokerModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center font-bold text-sm transition cursor-pointer">✕</button>
            </div>

            <!-- Drawer Form Body (Scrollable) -->
            <form id="editBrokerForm" method="POST" action="" class="p-6 overflow-y-auto flex-1 space-y-5 text-xs">
                @csrf
                @method('PUT')

                <!-- Section 1: Agency Specs -->
                <div class="space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#4F46E5] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-building text-xs"></i>
                        <span>1. Agency Information</span>
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Agency Name *</label>
                        <input type="text" id="edit_agency_name" name="agency_name" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-extrabold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Contact Person Name</label>
                        <input type="text" id="edit_contact_name" name="contact_name" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                    </div>
                </div>

                <!-- Section 2: Contact & Commission -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#0F172A] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-percent text-xs text-slate-500"></i>
                        <span>2. Contact & Terms</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Email Address *</label>
                            <input type="email" id="edit_broker_email" name="email" required class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5]">
                        </div>
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Phone Number *</label>
                            <input type="tel" id="edit_broker_phone" name="phone" required class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5]">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Commission Rate (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" id="edit_commission_rate" name="commission_rate" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                        </div>
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">New Password</label>
                            <input type="text" name="password" placeholder="Leave blank to keep current" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] focus:outline-none focus:border-[#4F46E5]">
                        </div>
                    </div>
                </div>
            </form>

            <!-- Footer Actions -->
            <div class="p-6 border-t border-[#E2E8F0] bg-slate-50/80 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('editBrokerModal').classList.add('hidden')" class="px-5 py-2.5 bg-white border border-[#E2E8F0] text-[#0F172A] btn-text rounded-xl hover:bg-slate-50 transition cursor-pointer">Cancel</button>
                <button type="submit" form="editBrokerForm" class="px-6 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text rounded-xl shadow-xs transition cursor-pointer">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    function switchBrokerView(mode) {
        const cardsCont = document.getElementById('cardsViewContainer');
        const tableCont = document.getElementById('tableViewContainer');
        const btnCards = document.getElementById('btnCardsView');
        const btnTable = document.getElementById('btnTableView');

        if (mode === 'cards') {
            cardsCont.classList.remove('hidden');
            tableCont.classList.add('hidden');
            btnCards.className = 'px-3 py-1.5 rounded-xl text-xs font-bold bg-white text-[#0F172A] shadow-2xs transition flex items-center space-x-1 cursor-pointer';
            btnTable.className = 'px-3 py-1.5 rounded-xl text-xs font-semibold text-[#64748B] hover:text-slate-900 transition flex items-center space-x-1 cursor-pointer';
        } else {
            cardsCont.classList.add('hidden');
            tableCont.classList.remove('hidden');
            btnTable.className = 'px-3 py-1.5 rounded-xl text-xs font-bold bg-white text-[#0F172A] shadow-2xs transition flex items-center space-x-1 cursor-pointer';
            btnCards.className = 'px-3 py-1.5 rounded-xl text-xs font-semibold text-[#64748B] hover:text-slate-900 transition flex items-center space-x-1 cursor-pointer';
        }
    }

    function toggleBrokerDropdown(id) {
        const targetMenu = document.getElementById('brokerDropdownMenu_' + id);
        if (!targetMenu) return;
        
        const isCurrentlyHidden = targetMenu.classList.contains('hidden');
        hideAllBrokerDropdowns();
        
        if (isCurrentlyHidden) {
            targetMenu.classList.remove('hidden');
        }
    }

    function hideAllBrokerDropdowns() {
        const allMenus = document.querySelectorAll('[id^="brokerDropdownMenu_"]');
        allMenus.forEach(menu => {
            menu.classList.add('hidden');
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="brokerDropdownMenu_"]') && !e.target.closest('button[onclick*="toggleBrokerDropdown"]')) {
            hideAllBrokerDropdowns();
        }
    });

    function openEditBrokerModal(broker) {
        document.getElementById('editBrokerForm').action = "/brokers/" + broker.id;
        document.getElementById('edit_agency_name').value = broker.agency_name || '';
        document.getElementById('edit_contact_name').value = (broker.user ? broker.user.name : broker.agency_name) || '';
        document.getElementById('edit_broker_email').value = broker.email || '';
        document.getElementById('edit_broker_phone').value = broker.phone || '';
        document.getElementById('edit_commission_rate').value = broker.commission_rate || '2.50';
        document.getElementById('editBrokerModal').classList.remove('hidden');
    }
</script>
@endsection
