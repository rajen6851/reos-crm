@extends('layouts.reos')

@section('title', 'CRM Interactive Schedule Calendar – REOS')

@section('content')
<!-- Include FullCalendar v6 CDN -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Breadcrumb & Top Action Header Bar (Matching Reference Screenshot) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-[#E2E8F0]">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>›</span>
                <span>Applications</span>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Calendar</span>
            </div>
            <h1 class="page-heading text-2xl">Calendar & Event Schedule</h1>
            <p class="body-text text-xs">View all scheduled property site visits, client follow-up calls, and booking milestones.</p>
        </div>

        <div class="flex items-center space-x-3">
            <button onclick="window.location.reload()" class="w-9 h-9 rounded-xl bg-white border border-[#E2E8F0] text-slate-500 hover:text-slate-900 shadow-2xs flex items-center justify-center text-xs transition" title="Refresh Calendar">
                <i class="fa-solid fa-rotate-right"></i>
            </button>

            <!-- Red + New Event Primary Button (Matching Reference Screenshot) -->
            <button onclick="document.getElementById('newEventModal').classList.remove('hidden')" class="px-5 py-2.5 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-[#DC2626] fa-plus text-xs"></i>
                <span>+ New Event</span>
            </button>
        </div>
    </div>

    <!-- Main Layout Grid: Left Sidebar Categories & Right Calendar (Exact Match to Reference Screenshot) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Sidebar Panel (3 Cols) -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Event Categories Filter Box -->
            <div class="reos-card p-5 bg-white space-y-4">
                <div>
                    <h3 class="section-heading text-base">Event Categories</h3>
                    <p class="body-text text-xs">Color legends for scheduled CRM activities</p>
                </div>

                <div class="space-y-2 text-xs font-semibold">
                    <div class="p-2.5 rounded-xl bg-emerald-50 text-[#059669] border border-emerald-200 flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#059669]"></span>
                        <span>Property Site Visits</span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-indigo-50 text-[#4F46E5] border border-indigo-200 flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#4F46E5]"></span>
                        <span>Client Follow-up Calls</span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-rose-50 text-[#DC2626] border border-rose-200 flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#DC2626]"></span>
                        <span>Key Client Meetings</span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-amber-50 text-[#D97706] border border-amber-200 flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#D97706]"></span>
                        <span>Project Launches</span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                        <span>HR & Staff Attendance</span>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events Summary Box (Matching Reference Screenshot) -->
            <div class="reos-card p-5 bg-white space-y-4">
                <h3 class="section-heading text-base">Upcoming Schedule</h3>

                <div class="space-y-3">
                    @forelse($upcomingEvents as $ue)
                    <div class="p-3 rounded-xl bg-slate-50 border border-[#E2E8F0] space-y-1 hover:border-[#DC2626] transition cursor-pointer">
                        <div class="text-xs font-bold text-[#0F172A] flex items-center justify-between">
                            <span class="truncate">{{ $ue['title'] }}</span>
                        </div>
                        <div class="text-[11px] text-[#64748B] flex items-center space-x-1.5 font-mono">
                            <i class="fa-regular fa-calendar text-[#4F46E5]"></i>
                            <span>{{ date('d M Y, h:i A', strtotime($ue['start'])) }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-xs text-slate-400 font-medium py-3 text-center">No upcoming events scheduled.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Main Interactive Calendar Container (9 Cols) -->
        <div class="lg:col-span-9 reos-card p-6 bg-white space-y-4">
            <div id="fullCalendarEngine" class="min-h-[600px]"></div>
        </div>
    </div>
</div>

<!-- Modal: Add New Calendar Event -->
<div id="newEventModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-6 border border-[#E2E8F0] shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-[#E2E8F0]">
            <h3 class="section-heading">Schedule New Event / Site Visit</h3>
            <button onclick="document.getElementById('newEventModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form action="{{ route('site-visits.index') }}" method="GET" class="space-y-3 text-xs">
            <div>
                <label class="form-label">Event Category</label>
                <select class="form-input">
                    <option value="site_visit">Schedule Property Site Visit</option>
                    <option value="follow_up">Client Follow-up Call</option>
                    <option value="meeting">Buyer Meeting / Negotiation</option>
                </select>
            </div>

            <div>
                <label class="form-label">Title / Note</label>
                <input type="text" placeholder="Enter event title..." class="form-input">
            </div>

            <div>
                <label class="form-label">Date & Time</label>
                <input type="datetime-local" value="{{ date('Y-m-d\TH:i') }}" class="form-input">
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t border-[#E2E8F0]">
                <button type="button" onclick="document.getElementById('newEventModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-[#0F172A] btn-text rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text rounded-xl shadow-xs">Save Event</button>
            </div>
        </form>
    </div>
</div>

<!-- FullCalendar Initialization Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('fullCalendarEngine');
        var eventsData = @json($events);

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            themeSystem: 'standard',
            events: eventsData,
            eventClick: function(info) {
                if (info.event.url) {
                    window.location.href = info.event.url;
                    info.jsEvent.preventDefault();
                }
            },
            height: 'auto'
        });
        calendar.render();
    });
</script>

<style>
    /* FullCalendar Custom Styling matching CRMS Theme */
    .fc-header-toolbar {
        margin-bottom: 1.5rem !important;
    }
    .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: 800 !important;
        color: #0F172A !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .fc-button-primary {
        background-color: #DC2626 !important;
        border-color: #DC2626 !important;
        border-radius: 0.75rem !important;
        font-weight: 700 !important;
        font-size: 0.75rem !important;
        padding: 0.5rem 0.875rem !important;
        box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
    }
    .fc-button-primary:hover {
        background-color: #B91C1C !important;
        border-color: #B91C1C !important;
    }
    .fc-button-primary:not(:disabled).fc-button-active {
        background-color: #0F172A !important;
        border-color: #0F172A !important;
    }
    .fc-daygrid-day-number {
        font-weight: 700;
        color: #0F172A;
        font-size: 0.75rem;
    }
    .fc-col-header-cell-cushion {
        font-weight: 800;
        color: #64748B;
        text-transform: uppercase;
        font-size: 0.7rem;
    }
    .fc-event {
        border-radius: 0.5rem !important;
        padding: 2px 4px !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
    }
</style>
@endsection
