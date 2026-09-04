@extends('layouts.reos')

@section('title', 'Channel Partner Brokers Directory - REOS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Header Banner -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-[#E2E8F0] shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Channel Partners</span>
            </div>
            <h1 class="page-heading text-2xl font-extrabold text-[#0F172A]">Channel Partner Brokers Directory</h1>
            <p class="body-text text-xs text-[#64748B] mt-0.5">Manage external broker agencies, commission agreements, and track submitted lead conversions</p>
        </div>

        <div class="flex items-center space-x-3">
            <!-- View Mode Switcher (Pure Vanilla JS) -->
            <div class="flex items-center bg-slate-100 p-1 rounded-2xl border border-[#E2E8F0]">
                <button type="button" id="btnCardsView" onclick="switchBrokerView('cards')" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-white text-[#0F172A] shadow-2xs transition flex items-center space-x-1 cursor-pointer">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>Cards</span>
                </button>
                <button type="button" id="btnTableView" onclick="switchBrokerView('table')" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-[#64748B] hover:text-slate-900 transition flex items-center space-x-1 cursor-pointer">
                    <i class="fa-solid fa-list"></i>
                    <span>Table</span>
                </button>
            </div>

            <button type="button" onclick="document.getElementById('addBrokerModal').classList.remove('hidden')" class="px-5 py-3 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-white text-xs"></i>
                <span>+ Register Partner Broker</span>
            </button>
        </div>
    </div>

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
                    $managerInitial = strtoupper(substr($contactName, 0, 1));
                @endphp
                <div class="bg-white rounded-3xl p-6 border border-[#E2E8F0] shadow-2xs hover:shadow-md transition space-y-4 relative">
                    <!-- Card Top Header: Avatar + Title/Rating + Red 3-Dots Dropdown Menu -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full bg-indigo-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs shrink-0">
                                {{ $initials }}
                            </div>
                            <div>
                                <div class="text-slate-900 font-extrabold text-base leading-tight">{{ $b->agency_name }}</div>
                                <div class="flex items-center space-x-1 text-xs text-amber-500 font-bold mt-0.5">
                                    <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                                    <span>4.5</span>
                                    <span class="text-[#64748B] font-mono text-[11px] font-semibold ml-1">({{ $b->broker_code }})</span>
                                </div>
                            </div>
                        </div>

                        <!-- Red 3-Dots Vertical Button & Floating Menu (Pure Vanilla JS - 100% Working Guaranteed) -->
                        <div class="relative">
                            <button type="button" onclick="event.stopPropagation(); toggleBrokerDropdown({{ $b->id }});" class="w-8 h-9 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white flex items-center justify-center transition shadow-md cursor-pointer active:scale-95" title="More Options">
                                <i class="fa-solid fa-ellipsis-vertical text-base pointer-events-none"></i>
                            </button>

                            <div id="brokerDropdownMenu_{{ $b->id }}" class="hidden absolute right-0 mt-2 w-36 bg-white rounded-2xl shadow-2xl border border-slate-200 p-2 z-50 text-xs space-y-1">
                                <!-- Option 1: Edit -->
                                <button type="button" onclick="event.stopPropagation(); openEditBrokerModal({{ json_encode($b) }}); hideAllBrokerDropdowns();" class="w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-100 rounded-xl font-bold flex items-center space-x-2 transition cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square text-slate-500 text-xs"></i>
                                    <span>Edit</span>
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
                                    <span>Preview</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Details List -->
                    <div class="space-y-2 text-xs text-[#0F172A] font-semibold pt-1">
                        <div class="flex items-center space-x-2 text-[#64748B]">
                            <i class="fa-regular fa-envelope text-slate-400 w-4 text-center"></i>
                            <span class="font-mono text-[#0F172A]">{{ $b->email }}</span>
                        </div>
                        <div class="flex items-center space-x-2 text-[#64748B]">
                            <i class="fa-solid fa-phone text-slate-400 w-4 text-center"></i>
                            <span class="font-mono text-[#0F172A]">{{ $b->phone }}</span>
                        </div>
                        <div class="flex items-center space-x-2 text-[#64748B]">
                            <i class="fa-solid fa-globe text-slate-400 w-4 text-center"></i>
                            <span class="text-[#0F172A]">India</span>
                        </div>
                    </div>

                    <!-- Category Pills -->
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-[#059669] border border-emerald-200 flex items-center space-x-1">
                            <span>Collab</span>
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center space-x-1">
                            <span>Rated</span>
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 flex items-center space-x-1">
                            <span>{{ number_format($b->commission_rate, 2) }}%</span>
                        </span>
                    </div>

                    <!-- Horizontal Separator -->
                    <div class="border-t border-[#E2E8F0] pt-3 flex items-center justify-between">
                        <!-- Left Action Icons Row -->
                        <div class="flex items-center space-x-3 text-slate-500 text-sm">
                            <a href="mailto:{{ $b->email }}" class="hover:text-[#4F46E5] transition" title="Send Email">
                                <i class="fa-regular fa-envelope"></i>
                            </a>
                            <a href="tel:{{ $b->phone }}" class="hover:text-[#059669] transition" title="Call Phone">
                                <i class="fa-solid fa-phone"></i>
                            </a>
                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $b->phone) }}" target="_blank" class="hover:text-emerald-600 transition" title="WhatsApp Chat">
                                <i class="fa-regular fa-comment-dots"></i>
                            </a>
                        </div>

                        <!-- Right Assigned Manager Initial Circle Badge -->
                        <div class="w-7 h-7 rounded-full bg-[#0F172A] text-white flex items-center justify-center font-extrabold text-xs shadow-xs" title="Assigned Contact: {{ $contactName }}">
                            {{ $managerInitial }}
                        </div>
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

                    <div>
                        <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Commission Rate (%) *</label>
                        <input type="number" step="0.01" min="0" max="100" name="commission_rate" value="2.50" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
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

                    <div>
                        <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Commission Rate (%) *</label>
                        <input type="number" step="0.01" min="0" max="100" id="edit_commission_rate" name="commission_rate" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
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
