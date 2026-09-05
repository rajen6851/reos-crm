@extends('layouts.reos')

@section('title', 'Follow-ups & Sales Tasks – REOS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ searchQuery: '' }">
    <!-- Header Banner -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-[#E2E8F0] shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Follow-ups & Tasks</span>
            </div>
            <h1 class="page-heading text-2xl font-extrabold text-[#0F172A]">Follow-ups & Sales Executive Tasks</h1>
            <p class="body-text text-xs text-[#64748B] mt-0.5">Pending lead call logs, customer follow-up tasks, call notes, and activity history</p>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <span class="px-4 py-2 bg-amber-50 text-amber-900 border border-amber-200 text-xs font-extrabold rounded-2xl flex items-center space-x-2">
                <i class="fa-solid fa-list-check text-amber-600"></i>
                <span>{{ $pendingFollowUps->count() }} Active Follow-up Tasks</span>
            </span>
        </div>
    </div>

    <!-- Summary Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Pending Follow-up Calls</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-1 font-mono">{{ $pendingFollowUps->count() }} Tasks</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg border border-amber-200"><i class="fa-solid fa-phone-volume text-amber-600"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Active Sales Staff</span>
                <div class="text-2xl font-extrabold text-[#059669] mt-1 font-mono">{{ $pendingFollowUps->pluck('assigned_to_user_id')->unique()->filter()->count() }} Staff</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#059669] flex items-center justify-center text-lg border border-emerald-200"><i class="fa-solid fa-user-tie text-[#059669]"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Total Call Activities Logged</span>
                <div class="text-2xl font-extrabold text-[#4F46E5] mt-1 font-mono">{{ $pendingFollowUps->sum(fn($f) => $f->calls->count()) }} Logs</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-[#4F46E5] flex items-center justify-center text-lg border border-indigo-100"><i class="fa-solid fa-clock-rotate-left text-[#4F46E5]"></i></span>
        </div>
    </div>

    <!-- Active Follow-up Tasks Directory Table -->
    <div class="bg-white rounded-3xl p-6 border border-[#E2E8F0] shadow-2xs space-y-4">
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
                                <button onclick="openCallModal({{ $fu->id }}, '{{ $fu->name }}')" class="px-3 py-1.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-1 cursor-pointer">
                                    <i class="fa-solid fa-phone text-white mr-1 text-xs"></i><span>Log Call</span>
                                </button>

                                <button onclick="openHistoryModal({{ json_encode($fu) }})" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 text-xs font-bold rounded-xl border border-amber-200 transition flex items-center space-x-1 cursor-pointer">
                                    <i class="fa-solid fa-clock-rotate-left text-amber-600 mr-1 text-xs"></i><span>History ({{ $fu->calls->count() }})</span>
                                </button>

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

<!-- Call Log Modal -->
<div id="callLogModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white w-full max-w-md p-6 rounded-3xl space-y-4 border border-[#E2E8F0] shadow-2xl">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-base font-extrabold text-[#0F172A]">Log Call / Follow-up Outcome</h3>
            <button onclick="document.getElementById('callLogModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-[#0F172A] font-bold hover:bg-slate-200 flex items-center justify-center">✕</button>
        </div>
        <form id="callLogForm" method="POST" action="" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block text-[#475569] mb-1 font-bold">Customer Name</label>
                <input type="text" id="callLogCustomerName" readonly class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-amber-900 font-bold">
            </div>
            <div>
                <label class="block text-[#475569] mb-1 font-bold">Call Summary / Discussion Notes *</label>
                <textarea name="notes" required rows="3" placeholder="Enter customer call response, floor preference, budget discussion..." class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]"></textarea>
            </div>
            <div>
                <label class="block text-[#475569] mb-1 font-bold">Update Pipeline Stage *</label>
                <select name="status" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-bold focus:outline-none focus:border-[#4F46E5]">
                    <option value="follow_up_scheduled">Keep Active Follow-up</option>
                    <option value="site_visit_scheduled">Schedule Site Visit</option>
                    <option value="booking_drafted">Draft Booking</option>
                    <option value="negotiation">Price Negotiation</option>
                    <option value="dropped">Not Interested / Closed</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('callLogModal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-[#0F172A] font-bold rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white font-bold rounded-xl shadow-xs">Save Call Log</button>
            </div>
        </form>
    </div>
</div>

<!-- History Drawer Modal -->
<div id="historyModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white w-full max-w-lg p-6 rounded-3xl space-y-4 border border-[#E2E8F0] shadow-2xl max-h-[85vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-base font-extrabold text-[#0F172A]">Activity History Timeline</h3>
            <button onclick="document.getElementById('historyModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-[#0F172A] font-bold hover:bg-slate-200 flex items-center justify-center">✕</button>
        </div>
        <div id="historyLogsContainer" class="space-y-3 text-xs">
            <!-- Populated via JS -->
        </div>
    </div>
</div>

<script>
    function openCallModal(leadId, name) {
        document.getElementById('callLogForm').action = "/leads/" + leadId + "/log-activity";
        document.getElementById('callLogCustomerName').value = name;
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
