@extends('layouts.reos')

@section('title', 'Site Visits Schedule - REOS')

@section('content')
<div class="space-y-8">
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Site Visits & Property Conduct Schedule</h1>
            <p class="text-xs text-slate-600 mt-1 font-medium">Track customer site visit appointments, feedback, and sales executive conducts</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 bg-sky-50 border border-sky-200 px-3.5 py-2 rounded-2xl">
            <span class="text-sky-900">Total Visits: {{ $siteVisits->count() }}</span>
        </div>
    </div>

    <!-- Site Visits Schedule Table -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-lg font-black text-slate-900">Scheduled & Completed Site Visits</h2>
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-800 font-extrabold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-4">Lead Name</th>
                        <th class="p-4">Project Requested</th>
                        <th class="p-4">Assigned Sales Exec</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Last Updated</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($siteVisits as $sv)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-900 flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-sky-50 text-sky-700 border border-sky-200 flex items-center justify-center font-black text-xs">
                                {{ strtoupper(substr($sv->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-slate-900 font-bold">{{ $sv->name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $sv->phone }}</div>
                            </div>
                        </td>
                        <td class="p-4 text-xs font-bold text-slate-900">
                            <i class="fa-solid fa-building text-indigo-600 mr-1"></i>{{ $sv->project->name ?? 'N/A' }}
                        </td>
                        <td class="p-4 text-xs">
                            <span class="px-3 py-1 font-bold rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                <i class="fa-solid fa-user text-slate-400 mr-1"></i>{{ $sv->assignedTo->name ?? 'Unassigned' }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-sky-100 text-sky-900 border border-sky-300 flex items-center w-fit space-x-1">
                                <i class="fa-solid fa-calendar-check text-sky-600 mr-1"></i>
                                <span>Site Visit</span>
                            </span>
                        </td>
                        <td class="p-4 text-xs text-slate-500 font-mono">
                            {{ $sv->updated_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="p-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="openCallModal({{ $sv->id }}, '{{ $sv->first_name }} {{ $sv->last_name }}')" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-900 text-xs font-bold rounded-xl border border-indigo-200 transition flex items-center space-x-1">
                                    <i class="fa-solid fa-phone text-indigo-600 mr-1"></i><span>Log Visit Feedback</span>
                                </button>

                                @if($sv->phone)
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $sv->phone);
                                    if (strlen($cleanPhone) === 10) {
                                        $cleanPhone = '91' . $cleanPhone;
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}?text=Hello%20{{ urlencode($sv->first_name) }},%20regarding%20your%20scheduled%20site%20visit..." target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs inline-flex items-center space-x-1 transition">
                                    <i class="fa-brands fa-whatsapp text-white mr-1 text-sm"></i><span>WhatsApp</span>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500 font-medium text-xs">No site visits recorded yet.</td>
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
                <h3 class="text-lg font-black text-slate-900">Log Site Visit Feedback</h3>
                <button onclick="document.getElementById('callLogModal').classList.add('hidden')" class="text-slate-500 hover:text-slate-900 font-bold">✕</button>
            </div>
            <form id="callLogForm" method="POST" action="" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 mb-1 font-bold">Customer Name</label>
                    <input type="text" id="callLogCustomerName" readonly class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-amber-900 font-bold">
                </div>
                <div>
                    <label class="block text-slate-700 mb-1 font-bold">Visit Outcome *</label>
                    <select name="call_outcome" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-slate-900 focus:outline-none focus:border-indigo-600 font-bold">
                        <option value="site_visit_conducted">Site Visit Conducted (Customer Tour Done)</option>
                        <option value="interested_after_visit">Interested After Site Visit</option>
                        <option value="scheduled_site_visit">Re-scheduled Site Visit</option>
                        <option value="connected">Negotiation / Offer Discussion</option>
                        <option value="not_interested">Not Interested</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-700 mb-1 font-bold">Schedule Follow-up Date/Time</label>
                    <input type="datetime-local" name="next_followup_at" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-slate-900 focus:outline-none focus:border-indigo-600">
                </div>
                <div>
                    <label class="block text-slate-700 mb-1 font-bold">Customer Feedback & Remarks</label>
                    <textarea name="notes" rows="3" placeholder="e.g. Liked 3BHK 5th floor unit, price sheet handed over." class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-slate-900 focus:outline-none focus:border-indigo-600"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" onclick="document.getElementById('callLogModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl border border-slate-200">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-extrabold rounded-xl shadow-xs">Save Visit Log</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openCallModal(leadId, leadName) {
        document.getElementById('callLogForm').action = "/leads/" + leadId + "/call";
        document.getElementById('callLogCustomerName').value = leadName;
        document.getElementById('callLogModal').classList.remove('hidden');
    }
</script>
@endsection
