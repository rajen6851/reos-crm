@extends('layouts.reos')

@section('title', 'Follow-ups & Sales Tasks - REOS')

@section('content')
<div class="space-y-8">
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Follow-ups & Sales Executive Tasks</h1>
            <p class="text-xs text-slate-600 mt-1 font-medium">Pending lead call logs, customer follow-up tasks, and executive call notes</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 bg-amber-50 border border-amber-200 px-3.5 py-2 rounded-2xl">
            <span class="text-amber-950">Active Tasks: {{ $pendingFollowUps->count() }}</span>
        </div>
    </div>

    <!-- Active Follow-up Tasks Table -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-lg font-black text-slate-900">Pending Lead Follow-ups</h2>
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-800 font-extrabold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-4">Customer Name</th>
                        <th class="p-4">Assigned Executive</th>
                        <th class="p-4">Current Lead Stage</th>
                        <th class="p-4">Last Activity</th>
                        <th class="p-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendingFollowUps as $fu)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-900 flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center font-black text-xs">
                                {{ strtoupper(substr($fu->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-slate-900 font-bold">{{ $fu->name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $fu->phone }}</div>
                            </div>
                        </td>
                        <td class="p-4 text-xs font-bold text-slate-700">
                            <i class="fa-solid fa-user text-slate-400 mr-1"></i>{{ $fu->assignedTo->name ?? 'Unassigned' }}
                        </td>
                        <td class="p-4 text-xs font-bold">
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 border border-slate-200 uppercase">
                                {{ str_replace('_', ' ', $fu->status) }}
                            </span>
                        </td>
                        <td class="p-4 text-xs text-slate-500 font-mono">
                            {{ $fu->updated_at->diffForHumans() }}
                        </td>
                        <td class="p-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="openCallModal({{ $fu->id }}, '{{ $fu->first_name }} {{ $fu->last_name }}')" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-900 text-xs font-bold rounded-xl border border-indigo-200 transition flex items-center space-x-1">
                                    <i class="fa-solid fa-phone text-indigo-600 mr-1"></i><span>Log Call</span>
                                </button>

                                <button onclick="openHistoryModal({{ json_encode($fu) }})" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 text-xs font-bold rounded-xl border border-amber-200 transition flex items-center space-x-1">
                                    <i class="fa-solid fa-clock-rotate-left text-amber-600 mr-1"></i><span>History ({{ $fu->calls->count() }})</span>
                                </button>

                                @if($fu->phone)
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $fu->phone);
                                    if (strlen($cleanPhone) === 10) {
                                        $cleanPhone = '91' . $cleanPhone;
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}?text=Hello%20{{ urlencode($fu->first_name) }},%20following%20up%20on%20your%20property%20enquiry..." target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs inline-flex items-center space-x-1 transition">
                                    <i class="fa-brands fa-whatsapp text-white mr-1 text-sm"></i><span>WhatsApp</span>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-500 font-medium text-xs">No pending follow-up tasks. Great job!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Call Log Modal -->
    <div id="callLogModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div class="bg-white w-full max-w-md p-6 rounded-3xl space-y-4 border border-slate-200 shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900">Log Call / Follow-up Outcome</h3>
                <button onclick="document.getElementById('callLogModal').classList.add('hidden')" class="text-slate-500 hover:text-slate-900 font-bold">✕</button>
            </div>
            <form id="callLogForm" method="POST" action="" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 mb-1 font-bold">Customer Name</label>
                    <input type="text" id="callLogCustomerName" readonly class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-amber-900 font-bold">
                </div>
                <div>
                    <label class="block text-slate-700 mb-1 font-bold">Call / Visit Outcome *</label>
                    <select name="call_outcome" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-slate-900 focus:outline-none focus:border-indigo-600 font-bold">
                        <option value="site_visit_conducted">Site Visit Conducted (Customer Tour Done)</option>
                        <option value="interested_after_visit">Interested After Site Visit</option>
                        <option value="connected">Connected & Discussed</option>
                        <option value="site_visit_scheduled">Site Visit Scheduled</option>
                        <option value="busy_callback">Busy / Callback Requested</option>
                        <option value="no_answer">No Answer / Ringing</option>
                        <option value="not_interested">Not Interested</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-700 mb-1 font-bold">Schedule Next Follow-up Date/Time</label>
                    <input type="datetime-local" name="next_followup_at" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-slate-900 focus:outline-none focus:border-indigo-600">
                </div>
                <div>
                    <label class="block text-slate-700 mb-1 font-bold">Call / Visit Remarks</label>
                    <textarea name="notes" rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-slate-900 focus:outline-none focus:border-indigo-600"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" onclick="document.getElementById('callLogModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl border border-slate-200">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-extrabold rounded-xl shadow-xs">Save Follow-up Log</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Call Log History Modal -->
    <div id="historyModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div class="bg-white w-full max-w-lg p-6 rounded-3xl space-y-4 border border-slate-200 shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Call & Site Visit History</h3>
                    <p id="historyCustomerTitle" class="text-xs text-amber-900 font-mono font-bold"></p>
                </div>
                <button onclick="document.getElementById('historyModal').classList.add('hidden')" class="text-slate-500 hover:text-slate-900 font-bold text-lg">✕</button>
            </div>

            <div id="historyLogsList" class="space-y-3 max-h-80 overflow-y-auto pr-1">
                <!-- Dynamic Logs Injection -->
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" onclick="document.getElementById('historyModal').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl border border-slate-200 text-xs">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openCallModal(leadId, leadName) {
        document.getElementById('callLogForm').action = "/leads/" + leadId + "/call";
        document.getElementById('callLogCustomerName').value = leadName;
        document.getElementById('callLogModal').classList.remove('hidden');
    }

    function openHistoryModal(lead) {
        document.getElementById('historyCustomerTitle').innerText = (lead.first_name || '') + ' ' + (lead.last_name || '') + ' (' + (lead.lead_code || '') + ')';
        var listContainer = document.getElementById('historyLogsList');
        listContainer.innerHTML = '';

        if (!lead.calls || lead.calls.length === 0) {
            listContainer.innerHTML = '<div class="p-6 text-center text-slate-600 font-medium text-xs rounded-xl bg-slate-50 border border-slate-200">No call logs or visit feedback recorded yet for this customer.</div>';
        } else {
            lead.calls.forEach(function(c) {
                var item = document.createElement('div');
                item.className = 'p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5 text-xs';
                
                var outcomeFormatted = (c.call_outcome || 'log').replace(/_/g, ' ').toUpperCase();
                var loggedBy = c.user ? c.user.name : 'Executive';
                var dateStr = c.called_at ? new Date(c.called_at).toLocaleString('en-IN') : 'Recent';

                item.innerHTML = `
                    <div class="flex justify-between items-center">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-indigo-100 text-indigo-900 border border-indigo-300 uppercase tracking-wider">${outcomeFormatted}</span>
                        <span class="text-[10px] font-mono text-slate-600 font-bold">${dateStr}</span>
                    </div>
                    <div class="text-slate-900 font-semibold">${c.notes || 'No remarks entered.'}</div>
                    <div class="text-[10px] text-amber-900 font-bold">Logged by: ${loggedBy}</div>
                `;
                listContainer.appendChild(item);
            });
        }

        document.getElementById('historyModal').classList.remove('hidden');
    }
</script>
@endsection
