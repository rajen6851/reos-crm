@extends('layouts.reos')

@section('title', 'Site Visits Schedule – REOS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ searchQuery: '', statusFilter: 'all' }">
    <!-- Header Banner -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-[#E2E8F0] shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Site Visits Schedule</span>
            </div>
            <h1 class="page-heading text-2xl font-extrabold text-[#0F172A]">Site Visits & Property Conduct Schedule</h1>
            <p class="body-text text-xs text-[#64748B] mt-0.5">Track customer site visit appointments, feedback, executive conducts, and conversion logs</p>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <span class="px-4 py-2 bg-sky-50 text-sky-900 border border-sky-200 text-xs font-extrabold rounded-2xl flex items-center space-x-2">
                <i class="fa-solid fa-calendar-check text-sky-600"></i>
                <span>{{ $siteVisits->count() }} Total Scheduled Visits</span>
            </span>
        </div>
    </div>

    <!-- Summary Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Total Visits Conducted</span>
                <div class="text-2xl font-extrabold text-[#0F172A] mt-1 font-mono">{{ $siteVisits->count() }} Appointments</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg border border-sky-100"><i class="fa-solid fa-[#0F172A] fa-location-dot text-sky-600"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Assigned Executives</span>
                <div class="text-2xl font-extrabold text-[#059669] mt-1 font-mono">{{ $siteVisits->pluck('assigned_to_user_id')->unique()->filter()->count() }} Staff</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#059669] flex items-center justify-center text-lg border border-emerald-200"><i class="fa-solid fa-user-tie text-[#059669]"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Projects Visited</span>
                <div class="text-2xl font-extrabold text-[#4F46E5] mt-1 font-mono">{{ $siteVisits->pluck('project_id')->unique()->filter()->count() }} Enclaves</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-[#4F46E5] flex items-center justify-center text-lg border border-indigo-100"><i class="fa-solid fa-building text-[#4F46E5]"></i></span>
        </div>
    </div>

    <!-- Site Visits Directory Table -->
    <div class="bg-white rounded-3xl p-6 border border-[#E2E8F0] shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-[#0F172A]">Scheduled & Conducted Site Visits</h2>

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
                        <th class="p-3.5">Lead Name & Phone</th>
                        <th class="p-3.5">Project Requested</th>
                        <th class="p-3.5">Assigned Sales Exec</th>
                        <th class="p-3.5">Visit Status</th>
                        <th class="p-3.5">Last Updated</th>
                        <th class="p-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($siteVisits as $sv)
                    <tr x-show="searchQuery === '' || '{{ strtolower($sv->name) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-slate-50/80 transition">
                        <td class="p-3.5 font-bold text-[#0F172A]">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-sky-50 text-sky-700 border border-sky-200 flex items-center justify-center font-extrabold text-xs">
                                    {{ strtoupper(substr($sv->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="text-[#0F172A] font-bold">{{ $sv->name }}</div>
                                    <div class="text-[11px] text-[#64748B] font-mono">{{ $sv->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3.5 font-bold text-[#0F172A]">
                            <i class="fa-solid fa-building text-[#4F46E5] mr-1"></i>{{ $sv->project->name ?? 'N/A' }}
                        </td>
                        <td class="p-3.5">
                            <span class="px-2.5 py-1 font-bold rounded-full bg-slate-50 text-[#475569] border border-slate-200 text-[10px]">
                                <i class="fa-solid fa-user text-slate-400 mr-1"></i>{{ $sv->assignedTo->name ?? 'Unassigned' }}
                            </span>
                        </td>
                        <td class="p-3.5">
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-full bg-sky-50 text-sky-900 border border-sky-200 flex items-center w-fit space-x-1">
                                <i class="fa-solid fa-calendar-check text-sky-600 mr-1"></i>
                                <span>Site Visit</span>
                            </span>
                        </td>
                        <td class="p-3.5 text-[#64748B] font-mono">
                            {{ $sv->updated_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="p-3.5 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="openCallModal({{ $sv->id }}, '{{ $sv->first_name ?? $sv->name }}')" class="px-3 py-1.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-1 cursor-pointer">
                                    <i class="fa-solid fa-phone text-white mr-1 text-xs"></i><span>Log Feedback</span>
                                </button>

                                @if($sv->phone)
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $sv->phone);
                                    if (strlen($cleanPhone) === 10) {
                                        $cleanPhone = '91' . $cleanPhone;
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}?text=Hello%20{{ urlencode($sv->name) }},%20regarding%20your%20scheduled%20site%20visit..." target="_blank" class="px-3 py-1.5 bg-[#059669] hover:bg-[#047857] text-white font-bold text-xs rounded-xl shadow-xs inline-flex items-center space-x-1 transition">
                                    <i class="fa-brands fa-whatsapp text-white mr-1 text-xs"></i><span>WhatsApp</span>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-[#64748B] font-medium text-xs">No site visits recorded yet.</td>
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
            <h3 class="text-base font-extrabold text-[#0F172A]">Log Site Visit Feedback</h3>
            <button onclick="document.getElementById('callLogModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-[#0F172A] font-bold hover:bg-slate-200 flex items-center justify-center">✕</button>
        </div>
        <form id="callLogForm" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-[#475569] mb-1">Customer Reaction & Feedback *</label>
                <textarea name="notes" required rows="3" placeholder="Enter site visit feedback, property interest level, floor preference..." class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]"></textarea>
            </div>
            <div>
                <label class="block font-bold text-[#475569] mb-1">Next Action Step *</label>
                <select name="next_action" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-bold focus:outline-none focus:border-[#4F46E5]">
                    <option value="follow_up_scheduled">Schedule Follow-up Call</option>
                    <option value="booking_drafted">Draft Unit Booking</option>
                    <option value="negotiation">Price Negotiation</option>
                    <option value="dropped">Not Interested / Closed</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('callLogModal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-[#0F172A] font-bold rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white font-bold rounded-xl shadow-xs">Save Feedback</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCallModal(leadId, name) {
        document.getElementById('callLogForm').action = "/leads/" + leadId + "/log-activity";
        document.getElementById('callLogModal').classList.remove('hidden');
    }
</script>
@endsection
