@extends('layouts.reos')

@section('title', 'Follow-ups & Sales Tasks â€“ UrbanProperty')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ searchQuery: '' }">
    <!-- Header Banner -->
    <div class="reos-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#2563EB]">Home</a>
                <span>â€º</span>
                <span class="text-[#0F172A] font-bold">Follow-ups & Tasks</span>
            </div>
            <h1 class="page-heading text-2xl font-extrabold text-[#0F172A]">Follow-ups & Sales Executive Tasks</h1>
            <p class="body-text text-xs text-[#64748B] mt-0.5">Pending lead call logs, customer follow-up tasks, call notes, and activity history</p>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <span class="px-3.5 py-1.5 bg-amber-50 text-amber-900 border border-amber-200 text-xs font-extrabold rounded-lg flex items-center space-x-2">
                <i class="fa-solid fa-list-check text-amber-600"></i>
                <span>{{ $pendingFollowUps->count() }} Active Follow-up Tasks</span>
            </span>
        </div>
    </div>

    <!-- Summary Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="reos-card p-4 flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Pending Follow-up Calls</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-1 font-mono">{{ $pendingFollowUps->count() }} Tasks</div>
            </div>
            <span class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg border border-amber-200"><i class="fa-solid fa-phone-volume text-amber-600"></i></span>
        </div>

        <div class="reos-card p-4 flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Active Sales Staff</span>
                <div class="text-2xl font-extrabold text-[#059669] mt-1 font-mono">{{ $pendingFollowUps->pluck('assigned_to_user_id')->unique()->filter()->count() }} Staff</div>
            </div>
            <span class="w-10 h-10 rounded-lg bg-emerald-50 text-[#059669] flex items-center justify-center text-lg border border-emerald-200"><i class="fa-solid fa-user-tie text-[#059669]"></i></span>
        </div>

        <div class="reos-card p-4 flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Total Call Activities Logged</span>
                <div class="text-2xl font-extrabold text-[#4F46E5] mt-1 font-mono">{{ $pendingFollowUps->sum(fn($f) => $f->calls->count()) }} Logs</div>
            </div>
            <span class="w-10 h-10 rounded-lg bg-indigo-50 text-[#4F46E5] flex items-center justify-center text-lg border border-indigo-100"><i class="fa-solid fa-clock-rotate-left text-[#4F46E5]"></i></span>
        </div>
    </div>

    <!-- Active Follow-up Tasks Directory Table -->
    <div class="reos-card p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-[#0F172A]">Pending Lead Follow-ups Directory</h2>

            <!-- Search Filter -->
            <div class="relative min-w-[240px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Search Customer Name..." class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl pl-9 pr-3 py-2 text-xs font-semibold text-[#0F172A] focus:outline-none focus:border-[#4F46E5]">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[#475569] font-extrabold uppercase border-b border-[#E2E8F0]">
                    <tr>
                        <th class="p-3.5">Customer Name & Phone</th>
                        <th class="p-3.5">Assigned Executive</th>
                        <th class="p-3.5">Current Lead Stage</th>
                        <th class="p-3.5">Last Activity</th>
                        <th class="p-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($pendingFollowUps as $fu)
                    <tr x-show="searchQuery === '' || '{{ strtolower($fu->name) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-slate-50/80 transition">
                        <td class="p-3.5 font-bold text-[#0F172A]">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-800 border border-amber-200 flex items-center justify-center font-extrabold text-xs">
                                    {{ strtoupper(substr($fu->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="text-[#0F172A] font-bold">{{ $fu->name }}</div>
                                    <div class="text-[11px] text-[#64748B] font-mono">{{ $fu->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3.5 font-bold text-[#0F172A]">
                            <i class="fa-solid fa-user text-slate-400 mr-1"></i>{{ $fu->assignedTo->name ?? 'Unassigned' }}
                        </td>
                        <td class="p-3.5">
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-[#0F172A] border border-slate-200 font-extrabold uppercase text-[10px]">
                                {{ str_replace('_', ' ', $fu->status) }}
                            </span>
                        </td>
                        <td class="p-3.5 text-[#64748B] font-mono">
                            {{ $fu->updated_at->diffForHumans() }}
                        </td>
                        <td class="p-3.5 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="openCallModal({{ json_encode($fu) }}, '{{ addslashes($fu->name) }}')" class="px-3 py-1.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-1 cursor-pointer">
                                    <i class="fa-solid fa-phone text-white mr-1 text-xs"></i><span>Log Call</span>
                                </button>
                                
                                <!-- Quick Actions Dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" @click.away="open = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-[#0F172A] text-xs font-bold rounded-xl transition flex items-center space-x-1 cursor-pointer">
                                        <i class="fa-solid fa-bolt text-amber-500 mr-1 text-xs"></i><span>Action</span>
                                        <i class="fa-solid fa-chevron-down text-[10px] ml-1"></i>
                                    </button>
                                    
                                    <div x-show="open" x-cloak class="absolute right-0 mt-1 w-48 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden z-20 text-left py-1">
                                        <a href="{{ route('leads.site-visit.create', $fu->id) }}" class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 flex items-center block">
                                            <i class="fa-solid fa-map-location-dot w-5 text-indigo-500"></i> Schedule Site Visit
                                        </a>
                                        @if(!auth()->user()->isSales())
                                        <a href="{{ route('leads.negotiate.create', $fu->id) }}" class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-amber-50 hover:text-amber-700 flex items-center block">
                                            <i class="fa-solid fa-handshake-angle w-5 text-amber-500"></i> Start Negotiation
                                        </a>
                                        <a href="{{ route('leads.convert.create', $fu->id) }}" class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 flex items-center block">
                                            <i class="fa-solid fa-money-check-dollar w-5 text-emerald-500"></i> Record Booking
                                        </a>
                                        @endif
                                        <div class="h-px bg-slate-100 my-1"></div>
                                        <a href="{{ route('leads.lost.create', $fu->id) }}" class="w-full text-left px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 flex items-center block">
                                            <i class="fa-solid fa-thumbs-down w-5"></i> Drop Lead
                                        </a>
                                    </div>
                                </div>



                                @if($fu->phone)
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $fu->phone);
                                    if (strlen($cleanPhone) === 10) {
                                        $cleanPhone = '91' . $cleanPhone;
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}?text=Hello%20{{ urlencode($fu->name) }},%20following%20up%20on%20your%20property%20enquiry..." target="_blank" class="px-3 py-1.5 bg-[#059669] hover:bg-[#047857] text-white font-bold text-xs rounded-xl shadow-xs inline-flex items-center space-x-1 transition">
                                    <i class="fa-brands fa-whatsapp text-white mr-1 text-xs"></i><span>WhatsApp</span>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-[#64748B] font-medium text-xs">No pending follow-up tasks. Great job!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Follow-up Modal -->
<div id="callLogModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white w-full max-w-3xl overflow-hidden shadow-2xl border border-slate-300">
        <!-- Header (Blue like the screenshot) -->
        <div class="flex justify-between items-start px-5 py-4 bg-[#4A86BA] text-white relative">
            <div>
                <h3 class="text-xl font-bold mb-2">Follow-up</h3>
                <div class="flex items-center space-x-6 text-[13px] font-semibold">
                    <div>Customer Name: <span id="callModalLeadName"></span></div>
                    <div>Phone No. <span id="callModalLeadPhone"></span></div>
                </div>
            </div>
            <button onclick="document.getElementById('callLogModal').classList.add('hidden')" class="text-white hover:text-slate-200 text-lg absolute right-4 top-3">âœ•</button>
        </div>
        <form id="callLogForm" method="POST" action="" class="p-5 text-sm" x-data="{ pipelineStage: 'IN FOLLOWUP' }">
            @csrf
            <input type="hidden" name="call_outcome" value="connected">

            <div class="grid grid-cols-3 gap-4 mb-4">
                <!-- Inquiry Status -->
                <div>
                    <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Inquiry Status <span class="text-orange-500">*</span></label>
                    <select name="status" x-model="pipelineStage" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                        <option value="Meeting at Client Place">Meeting at Client Place</option>
                        <option value="Not Interested">Not Interested</option>
                        <option value="WARM">WARM</option>
                        <option value="IN FOLLOWUP">IN FOLLOWUP</option>
                        <option value="READY TO VISIT">READY TO VISIT</option>
                        <option value="NOT CONNECTED">NOT CONNECTED</option>
                    </select>
                </div>
                <!-- Next Followup Date -->
                <div>
                    <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Next Followup Date <span class="text-orange-500">*</span></label>
                    <input type="datetime-local" name="next_followup_at" required class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                </div>
                <!-- Budget Upto -->
                <div>
                    <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Budget Upto</label>
                    <select name="budget_max" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                        <option value="">Select Budget Upto</option>
                        <option value="1500000">15 Lacs</option>
                        <option value="3000000">30 Lacs</option>
                        <option value="5000000">50 Lacs</option>
                        <option value="10000000">1 Crore</option>
                    </select>
                </div>
            </div>

            <!-- Remarks & Save Button -->
            <div class="flex items-end space-x-3 mb-6">
                <div class="grow">
                    <textarea name="notes" rows="3" class="w-full bg-white border border-slate-300 p-2 focus:outline-none focus:border-blue-500 text-[13px] resize-none" placeholder="Enter Followup Remark" required></textarea>
                </div>
                <button type="submit" class="bg-[#4A86BA] hover:bg-[#386b99] text-white font-bold py-1.5 px-6 rounded shadow shrink-0 mb-4">
                    Save
                </button>
            </div>

            <!-- Recent Follow Up Detail Table -->
            <div class="bg-[#DCEBF6] font-bold text-center text-[#1F2937] py-1 text-[14px]">
                Recent Follow Up Detail
            </div>
            <div class="max-h-40 overflow-y-auto bg-slate-100 p-0 border border-t-0 border-slate-200">
                <table class="w-full text-left text-[12px] text-slate-700">
                    <thead class="bg-slate-200 text-slate-800 font-bold sticky top-0 border-b border-slate-300">
                        <tr>
                            <th class="py-2 px-3 text-center w-10">S/No.</th>
                            <th class="py-2 px-3">Type</th>
                            <th class="py-2 px-3">Followup Date</th>
                            <th class="py-2 px-3">Remark</th>
                            <th class="py-2 px-3">Next Schedule date</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3">CreatedBy</th>
                        </tr>
                    </thead>
                    <tbody id="modalHistoryLogsContainer" class="divide-y divide-slate-300 bg-white">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>
            <div class="text-[11px] text-red-500 font-semibold mt-4">
                *ccoa: customer click on ad, *ccoaa: customer click on ad again (open lead &Rightarrow; dump lead)
            </div>
        </form>
    </div>
</div>

<!-- History Drawer Modal -->
<div id="historyModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white w-full max-w-lg p-6 rounded-3xl space-y-4 border border-[#E2E8F0] shadow-2xl max-h-[85vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-base font-extrabold text-[#0F172A]">Activity History Timeline</h3>
            <button onclick="document.getElementById('historyModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-[#0F172A] font-bold hover:bg-slate-200 flex items-center justify-center">âœ•</button>
        </div>
        <div id="historyLogsContainer" class="space-y-3 text-xs">
            <!-- Populated via JS -->
        </div>
    </div>
</div>

<script>
    function openCallModal(lead, leadName) {
        document.getElementById('callLogForm').action = "/leads/" + lead.id + "/call";
        document.getElementById('callModalLeadName').textContent = leadName || (lead.first_name + ' ' + (lead.last_name || ''));
        document.getElementById('callModalLeadPhone').textContent = lead.phone || 'N/A';
        
        const container = document.getElementById('modalHistoryLogsContainer');
        container.innerHTML = '';
        const calls = lead.calls || [];
        if(calls.length === 0) {
            container.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-slate-400 italic">No previous call logs recorded.</td></tr>';
        } else {
            calls.forEach((c, index) => {
                const tr = document.createElement('tr');
                const dateFormatted = c.created_at ? new Date(c.created_at).toLocaleString() : '-';
                const nextSchedule = c.next_followup_at ? new Date(c.next_followup_at).toLocaleString() : '-';
                tr.innerHTML = `
                    <td class="py-2 px-3 text-center">${index + 1}</td>
                    <td class="py-2 px-3 text-slate-800">Lead</td>
                    <td class="py-2 px-3">${dateFormatted}</td>
                    <td class="py-2 px-3 font-semibold text-slate-900">${c.notes || c.description || ''}</td>
                    <td class="py-2 px-3 text-slate-800">${nextSchedule}</td>
                    <td class="py-2 px-3 text-slate-800 font-semibold uppercase">${c.lead_status_snapshot || lead.status.toUpperCase()}</td>
                    <td class="py-2 px-3 text-slate-800 font-bold uppercase">${c.user ? c.user.name : 'SYSTEM'}</td>
                `;
                container.appendChild(tr);
            });
        }
        
        document.getElementById('callLogModal').classList.remove('hidden');
    }

    function openHistoryModal(lead) {
        const container = document.getElementById('historyLogsContainer');
        container.innerHTML = '';
        const calls = lead.calls || [];
        if(calls.length === 0) {
            container.innerHTML = '<div class="p-4 text-center text-slate-400 italic">No previous call logs recorded for ' + lead.name + '.</div>';
        } else {
            calls.forEach(c => {
                const item = document.createElement('div');
                item.className = 'p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-1';
                item.innerHTML = `<div class="flex justify-between font-bold text-[#0F172A]">
                                    <span>${c.user ? c.user.name : 'Executive Log'}</span>
                                    <span class="text-[11px] text-[#64748B] font-mono">${new Date(c.created_at).toLocaleString()}</span>
                                  </div>
                                  <p class="text-[#475569] leading-relaxed">${c.notes || 'Activity logged'}</p>`;
                container.appendChild(item);
            });
        }
        document.getElementById('historyModal').classList.remove('hidden');
    }
</script>
@endsection
