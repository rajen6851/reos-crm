@extends('layouts.reos')

@section('title', 'Site Visits – UrbanProperty CRM')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ searchQuery: '', isFilterOpen: true }">

    {{-- ═══ HEADER ═══ --}}
    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">Home</a>
                <span>›</span>
                <span class="text-slate-900 font-bold">Site Visits</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-sky-600 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-location-dot text-white text-base"></i>
                </div>
                <span>Site Visits & Property Conduct</span>
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-1">Track site visits, upload visit photos, and submit customer feedback with star ratings</p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <span class="px-4 py-2.5 bg-sky-50 text-sky-900 border border-sky-200 text-xs font-bold rounded-xl flex items-center space-x-2">
                <i class="fa-solid fa-calendar-check text-sky-600"></i>
                <span>{{ $siteVisits->count() }} Total Visits</span>
            </span>
        </div>
    </div>

    {{-- ═══ METRICS ═══ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex justify-between items-center">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Scheduled</div>
                <div class="text-2xl font-extrabold text-slate-900 mt-1 font-mono">{{ $siteVisits->count() }}</div>
            </div>
            <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base border border-sky-100"><i class="fa-solid fa-location-dot"></i></span>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex justify-between items-center">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">With Photos</div>
                <div class="text-2xl font-extrabold text-emerald-600 mt-1 font-mono">
                    {{ $siteVisits->filter(fn($sv) => $sv->siteVisits->first()?->visit_images && count($sv->siteVisits->first()->visit_images ?? []) > 0)->count() }}
                </div>
            </div>
            <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base border border-emerald-100"><i class="fa-solid fa-camera"></i></span>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex justify-between items-center">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Feedback Given</div>
                <div class="text-2xl font-extrabold text-indigo-600 mt-1 font-mono">
                    {{ $siteVisits->filter(fn($sv) => !empty($sv->siteVisits->first()?->feedback_notes))->count() }}
                </div>
            </div>
            <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base border border-indigo-100"><i class="fa-solid fa-comment-dots"></i></span>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex justify-between items-center">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Projects Visited</div>
                <div class="text-2xl font-extrabold text-amber-600 mt-1 font-mono">{{ $siteVisits->pluck('interested_project_id')->unique()->filter()->count() }}</div>
            </div>
            <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base border border-amber-100"><i class="fa-solid fa-building"></i></span>
        </div>
    </div>

    {{-- ═══ ADVANCED SEARCH PANEL ═══ --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <button @click="isFilterOpen = !isFilterOpen" class="w-full flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100/50 transition">
            <div class="flex items-center space-x-2 text-sm font-extrabold text-blue-600">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Search Panel</span>
            </div>
            <i class="fa-solid" :class="isFilterOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>

        <div x-show="isFilterOpen" x-collapse class="p-5 border-t border-slate-200">
            <form action="{{ route('site-visits.index') }}" method="GET" class="space-y-5 text-xs font-semibold">
                
                {{-- Row 1 --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Source</label>
                        <select name="source" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select Source</option>
                            <option value="digital" {{ request('source') == 'digital' ? 'selected' : '' }}>Digital</option>
                            <option value="walk_in" {{ request('source') == 'walk_in' ? 'selected' : '' }}>Walk-in</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Lead</label>
                        <select name="lead_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select Lead</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Assign By</label>
                        <select name="assigned_by" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select Assign By</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('assigned_by') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Site Visited By</label>
                        <select name="site_visited_by" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">-SELECT-</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('site_visited_by') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 2 --}}
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Interested in</label>
                        <select name="interested_in" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select Interested in</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Inquiry Code</label>
                        <input type="text" name="inquiry_code" value="{{ request('inquiry_code') }}" placeholder="Select inquiry code" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Inquiry Status</label>
                        <select name="inquiry_status" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select inquiry status</option>
                            <option value="site_visit" {{ request('inquiry_status') == 'site_visit' ? 'selected' : '' }}>Site Visit</option>
                            <option value="negotiation" {{ request('inquiry_status') == 'negotiation' ? 'selected' : '' }}>Negotiation</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Visit From</label>
                        <input type="date" name="visit_from" value="{{ request('visit_from') }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Visit To</label>
                        <input type="date" name="visit_to" value="{{ request('visit_to') }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>

                {{-- Row 3 --}}
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Next Follow Dt</label>
                        <input type="date" name="next_follow_dt" value="{{ request('next_follow_dt') }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Project</label>
                        <select name="project_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select Project</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Budget Upto</label>
                        <select name="budget" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select Budget Upto</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Status</label>
                        <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">State</label>
                        <select name="state" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select State</option>
                        </select>
                    </div>
                </div>

                {{-- Row 4 --}}
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-slate-700">City</label>
                        <select name="city" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select City</option>
                            @foreach($cities as $c)
                                <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Purpose</label>
                        <select name="purpose" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">- ALL -</option>
                            <option value="Residential" {{ request('purpose') == 'Residential' ? 'selected' : '' }}>Residential</option>
                            <option value="Commercial" {{ request('purpose') == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Locality</label>
                        <select name="locality" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">- ALL -</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Broker</label>
                        <select name="broker" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">- ALL -</option>
                            @foreach($brokers as $b)
                                <option value="{{ $b->id }}" {{ request('broker') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Size/Area</label>
                        <select name="size_area" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">- ALL -</option>
                        </select>
                    </div>
                </div>

                {{-- Row 5 --}}
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-slate-700">FB Page</label>
                        <select name="fb_page" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">- ALL -</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">FB Form</label>
                        <select name="fb_form" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">- ALL -</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Resource</label>
                        <select name="resource" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select Resource</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Mode</label>
                        <select name="mode" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select All</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-slate-700">Stage</label>
                        <select name="stage" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Select All</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition">
                        <i class="fa-solid fa-filter mr-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ VISITS TABLE ═══ --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-5 border-b border-slate-100">
            <h2 class="text-base font-extrabold text-slate-900">Scheduled & Conducted Site Visits</h2>
            <div class="relative min-w-[240px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Search customer name..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs font-semibold text-slate-900 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-extrabold uppercase border-b border-slate-200 text-[10px]">
                    <tr>
                        <th class="p-3.5">Customer</th>
                        <th class="p-3.5">Project</th>
                        <th class="p-3.5">Executive</th>
                        <th class="p-3.5">Feedback & Rating</th>
                        <th class="p-3.5">Visit Photos</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($siteVisits as $sv)
                    @php
                        $latestVisit = $sv->siteVisits->first();
                        $images      = $latestVisit?->visit_images ?? [];
                        $rating      = $latestVisit?->customer_rating;
                        $hasFeedback = !empty($latestVisit?->feedback_notes);
                    @endphp
                    <tr x-show="searchQuery === '' || '{{ strtolower($sv->name ?? '') }}'.includes(searchQuery.toLowerCase())" class="hover:bg-slate-50/60 transition">
                        {{-- Customer --}}
                        <td class="p-3.5">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-sky-100 text-sky-700 border border-sky-200 flex items-center justify-center font-extrabold text-xs shrink-0">
                                    {{ strtoupper(substr($sv->name ?? 'NA', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $sv->name }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">{{ $sv->phone }}</div>
                                </div>
                            </div>
                        </td>
                        {{-- Project --}}
                        <td class="p-3.5">
                            <div class="font-bold text-slate-900">{{ $sv->project->name ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $sv->project->city ?? '' }}</div>
                        </td>
                        {{-- Executive --}}
                        <td class="p-3.5">
                            <span class="px-2.5 py-1 font-bold rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-[10px]">
                                <i class="fa-solid fa-user text-slate-400 mr-1"></i>{{ $sv->assignedTo->name ?? 'Unassigned' }}
                            </span>
                        </td>
                        {{-- Feedback & Rating --}}
                        <td class="p-3.5 max-w-[200px]">
                            @if($hasFeedback)
                                @if($rating)
                                <div class="flex items-center space-x-0.5 mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star text-[10px] {{ $i <= $rating ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                    @endfor
                                    <span class="text-[10px] text-slate-500 ml-1 font-mono font-bold">{{ $rating }}/5</span>
                                </div>
                                @endif
                                <p class="text-slate-600 text-[11px] leading-relaxed line-clamp-2">{{ $latestVisit->feedback_notes }}</p>
                                @if($latestVisit->feedback_submitted_at)
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $latestVisit->feedback_submitted_at->diffForHumans() }}</div>
                                @endif
                            @else
                                <span class="text-slate-400 italic text-[11px]">— No feedback yet</span>
                            @endif
                        </td>
                        {{-- Photos --}}
                        <td class="p-3.5">
                            @if(count($images) > 0)
                            <div class="flex -space-x-2">
                                @foreach(array_slice($images, 0, 4) as $imgPath)
                                <img src="{{ asset('storage/' . $imgPath) }}"
                                     class="w-9 h-9 rounded-lg object-cover border-2 border-white shadow-sm cursor-pointer hover:scale-110 transition-transform"
                                     onclick="openGallery({{ json_encode($images) }})"
                                     alt="Visit photo">
                                @endforeach
                                @if(count($images) > 4)
                                <div class="w-9 h-9 rounded-lg bg-slate-700 text-white text-[9px] font-bold flex items-center justify-center border-2 border-white cursor-pointer hover:bg-slate-900 transition"
                                     onclick="openGallery({{ json_encode($images) }})">
                                    +{{ count($images) - 4 }}
                                </div>
                                @endif
                            </div>
                            <div class="text-[10px] text-emerald-600 font-semibold mt-1">
                                <i class="fa-solid fa-images text-[9px]"></i> {{ count($images) }} photo{{ count($images) > 1 ? 's' : '' }}
                            </div>
                            @else
                            <span class="text-slate-400 text-[11px] italic">No photos</span>
                            @endif
                        </td>
                        {{-- Status --}}
                        <td class="p-3.5">
                            @php
                                $statusColors = [
                                    'site_visit'  => 'bg-sky-50 text-sky-800 border-sky-200',
                                    'negotiation' => 'bg-amber-50 text-amber-800 border-amber-200',
                                    'converted'   => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                    'lost'        => 'bg-rose-50 text-rose-800 border-rose-200',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-full border {{ $statusColors[$sv->status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                {{ ucwords(str_replace('_', ' ', $sv->status)) }}
                            </span>
                        </td>
                        {{-- Actions --}}
                        <td class="p-3.5 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="openFeedbackModal({{ $sv->id }}, '{{ addslashes($sv->name) }}')"
                                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-1">
                                    <i class="fa-solid fa-{{ $hasFeedback ? 'pen' : 'camera' }} text-xs"></i>
                                    <span>{{ $hasFeedback ? 'Update' : 'Log' }} Feedback</span>
                                </button>
                                @if($sv->phone)
                                @php $cleanPhone = preg_replace('/[^0-9]/', '', $sv->phone); if(strlen($cleanPhone) === 10) $cleanPhone = '91'.$cleanPhone; @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}?text=Hello%20{{ urlencode($sv->name) }},%20regarding%20your%20site%20visit..." target="_blank"
                                   class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs inline-flex items-center space-x-1 transition">
                                    <i class="fa-brands fa-whatsapp text-xs"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-calendar-xmark text-3xl text-slate-200 mb-3 block"></i>
                            No site visits scheduled yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══ RECENT ACTIVITY ═══ --}}
    @if($recentVisitLogs->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 space-y-4">
        <h2 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
            <i class="fa-solid fa-clock-rotate-left text-indigo-500"></i>
            <span>Recent Feedback Activity</span>
        </h2>
        <div class="space-y-2 text-xs">
            @foreach($recentVisitLogs as $log)
            <div class="flex items-start space-x-3 p-3 rounded-xl bg-slate-50 border border-slate-200">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center shrink-0 text-xs">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-slate-800 truncate">{{ $log->description }}</div>
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $log->created_at->format('d M Y, h:i A') }} • {{ $log->user->name ?? 'System' }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- ════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Log Site Visit Feedback + Images                            --}}
{{-- ════════════════════════════════════════════════════════════════════ --}}
<div id="feedbackModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-xl rounded-2xl border border-slate-200 shadow-2xl overflow-hidden flex flex-col" style="max-height:90vh">

        {{-- Header --}}
        <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200 bg-indigo-50 shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center">
                    <i class="fa-solid fa-camera text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Log Site Visit Feedback</h3>
                    <p class="text-[10px] text-slate-500 font-medium" id="feedbackModalSubtitle">Photos, rating & notes</p>
                </div>
            </div>
            <button onclick="closeFeedbackModal()" class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-slate-800 flex items-center justify-center font-bold text-xl">&times;</button>
        </div>

        {{-- Scrollable Form --}}
        <div class="overflow-y-auto flex-1">
            <form id="feedbackForm" method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-5 text-xs">
                @csrf

                {{-- ⭐ Star Rating --}}
                <div class="space-y-2">
                    <label class="block font-bold text-slate-800">Customer Interest Rating</label>
                    <div class="flex items-center space-x-2">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" data-star="{{ $i }}" onclick="setRating({{ $i }})"
                                class="star-btn w-10 h-10 rounded-xl border-2 border-slate-200 bg-white text-slate-300 hover:text-amber-400 hover:border-amber-300 transition text-xl flex items-center justify-center">
                            <i class="fa-solid fa-star"></i>
                        </button>
                        @endfor
                        <span id="ratingLabel" class="text-slate-400 font-semibold ml-2">Not rated</span>
                    </div>
                    <input type="hidden" name="customer_rating" id="ratingInput" value="">
                    <div class="grid grid-cols-5 gap-1 text-[9px] text-slate-400 text-center font-semibold">
                        <span>Not Interested</span><span>Slightly</span><span>Moderate</span><span>Interested</span><span>Very Hot 🔥</span>
                    </div>
                </div>

                {{-- Feedback Text --}}
                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-800">Customer Reaction & Feedback <span class="text-rose-500">*</span></label>
                    <textarea name="feedback_notes" required rows="3"
                              placeholder="Customer's reaction, property interest level, floor/unit preference, objections raised, budget concerns..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500 resize-none"></textarea>
                </div>

                {{-- Next Action --}}
                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-800">Next Action Step <span class="text-rose-500">*</span></label>
                    <select name="next_action" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-slate-900 font-bold focus:outline-none focus:border-indigo-500">
                        <option value="follow_up_scheduled">📞 Schedule Follow-up Call</option>
                        <option value="negotiation">💰 Move to Price Negotiation</option>
                        <option value="booking_drafted">📋 Draft Unit Booking</option>
                        <option value="dropped">❌ Not Interested / Close Lead</option>
                    </select>
                </div>

                {{-- 📷 Image Upload --}}
                <div class="space-y-2">
                    <label class="block font-bold text-slate-800">
                        <i class="fa-solid fa-images text-indigo-500 mr-1"></i>
                        Visit Photos <span class="text-slate-400 font-normal">(optional — up to 10 images, 5 MB each)</span>
                    </label>

                    <div id="imgDropZone"
                         onclick="document.getElementById('visitImagesInput').click()"
                         ondragover="event.preventDefault(); this.classList.add('border-indigo-400','bg-indigo-50')"
                         ondragleave="this.classList.remove('border-indigo-400','bg-indigo-50')"
                         ondrop="handleImgDrop(event)"
                         class="cursor-pointer border-2 border-dashed border-slate-300 rounded-xl p-5 text-center hover:border-indigo-400 hover:bg-indigo-50 transition-all">
                        <i class="fa-solid fa-cloud-arrow-up text-slate-400 text-2xl mb-2"></i>
                        <p class="text-slate-500 font-semibold text-[11px]">Click or drag & drop site visit photos</p>
                        <p class="text-slate-400 text-[10px] mt-0.5">JPG, PNG, WEBP — Max 5 MB each</p>
                    </div>

                    <input type="file" id="visitImagesInput" name="visit_images[]"
                           accept=".jpg,.jpeg,.png,.webp" multiple class="hidden"
                           onchange="handleImgSelect(this)">

                    <div id="imgPreviewGrid" class="hidden grid grid-cols-4 gap-2"></div>
                    <div id="imgCountBadge" class="hidden text-[11px] text-indigo-600 font-bold"></div>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeFeedbackModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition flex items-center space-x-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Submit Feedback & Photos</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- GALLERY LIGHTBOX --}}
<div id="galleryModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4" onclick="if(event.target===this)closeGallery()">
    <div class="relative max-w-4xl w-full">
        <button onclick="closeGallery()" class="absolute -top-10 right-0 text-white font-bold text-2xl hover:text-slate-300">&times;</button>
        <img id="galleryMainImg" src="" class="w-full max-h-[70vh] object-contain rounded-xl shadow-2xl" alt="Visit photo">
        <button onclick="galleryPrev()" class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/80 transition"><i class="fa-solid fa-chevron-left"></i></button>
        <button onclick="galleryNext()" class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/80 transition"><i class="fa-solid fa-chevron-right"></i></button>
        <div id="galleryThumbs" class="flex space-x-2 mt-3 overflow-x-auto pb-1 justify-center"></div>
        <div id="galleryCounter" class="text-center text-white/60 text-xs font-mono mt-2"></div>
    </div>
</div>

<script>
// ─── FEEDBACK MODAL ──────────────────────────────────────────────────────────
var selectedFiles = [];
var currentRating = 0;

function openFeedbackModal(leadId, name) {
    document.getElementById('feedbackForm').action = '/site-visits/' + leadId + '/feedback';
    document.getElementById('feedbackModalSubtitle').textContent = name + ' — feedback & photos';
    resetFeedbackModal();
    document.getElementById('feedbackModal').classList.remove('hidden');
}
function closeFeedbackModal() {
    document.getElementById('feedbackModal').classList.add('hidden');
}
function resetFeedbackModal() {
    selectedFiles = []; currentRating = 0;
    document.getElementById('ratingInput').value = '';
    document.getElementById('ratingLabel').textContent = 'Not rated';
    document.getElementById('visitImagesInput').value = '';
    document.getElementById('imgPreviewGrid').innerHTML = '';
    document.getElementById('imgPreviewGrid').classList.add('hidden');
    document.getElementById('imgCountBadge').classList.add('hidden');
    document.getElementById('imgDropZone').classList.remove('hidden');
    document.querySelectorAll('.star-btn').forEach(function(b) {
        b.classList.remove('text-amber-400','border-amber-400','bg-amber-50');
        b.classList.add('text-slate-300','border-slate-200');
    });
}

// ─── STAR RATING ─────────────────────────────────────────────────────────────
var ratingLabels = ['','Not Interested','Slightly Interested','Moderate Interest','Interested','Very Interested! 🔥'];
function setRating(star) {
    currentRating = star;
    document.getElementById('ratingInput').value = star;
    document.getElementById('ratingLabel').textContent = ratingLabels[star];
    document.querySelectorAll('.star-btn').forEach(function(b) {
        var s = parseInt(b.dataset.star);
        if (s <= star) {
            b.classList.add('text-amber-400','border-amber-400','bg-amber-50');
            b.classList.remove('text-slate-300','border-slate-200');
        } else {
            b.classList.remove('text-amber-400','border-amber-400','bg-amber-50');
            b.classList.add('text-slate-300','border-slate-200');
        }
    });
}

// ─── IMAGE UPLOAD ─────────────────────────────────────────────────────────────
function handleImgSelect(input) { addImgFiles(Array.from(input.files)); }
function handleImgDrop(event) {
    event.preventDefault();
    document.getElementById('imgDropZone').classList.remove('border-indigo-400','bg-indigo-50');
    var files = Array.from(event.dataTransfer.files).filter(function(f) { return f.type.match(/image\/(jpeg|png|webp)/); });
    addImgFiles(files);
}
function addImgFiles(files) {
    var remaining = 10 - selectedFiles.length;
    var toAdd = files.slice(0, remaining);
    if (toAdd.length < files.length) alert('Max 10 photos. Adding first ' + toAdd.length + '.');
    toAdd.forEach(function(f) {
        if (f.size > 5 * 1024 * 1024) { alert(f.name + ' exceeds 5MB limit.'); return; }
        selectedFiles.push(f);
    });
    renderImgPreviews();
    syncImgInput();
}
function renderImgPreviews() {
    var grid = document.getElementById('imgPreviewGrid');
    grid.innerHTML = '';
    var hasFiles = selectedFiles.length > 0;
    grid.classList.toggle('hidden', !hasFiles);
    document.getElementById('imgCountBadge').classList.toggle('hidden', !hasFiles);
    document.getElementById('imgDropZone').classList.toggle('hidden', selectedFiles.length >= 10);
    document.getElementById('imgCountBadge').textContent = selectedFiles.length + ' photo' + (selectedFiles.length > 1 ? 's' : '') + ' selected';
    selectedFiles.forEach(function(file, idx) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var div = document.createElement('div');
            div.className = 'relative group rounded-xl overflow-hidden border-2 border-slate-200 aspect-square';
            div.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">' +
                '<button type="button" onclick="removeImg(' + idx + ')" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-rose-600 text-white text-[10px] font-bold flex items-center justify-center opacity-0 group-hover:opacity-100 transition">&times;</button>';
            grid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
function removeImg(idx) { selectedFiles.splice(idx, 1); renderImgPreviews(); syncImgInput(); }
function syncImgInput() {
    var dt = new DataTransfer();
    selectedFiles.forEach(function(f) { dt.items.add(f); });
    document.getElementById('visitImagesInput').files = dt.files;
}

// ─── GALLERY LIGHTBOX ────────────────────────────────────────────────────────
var galleryImages = []; var galleryIdx = 0;
var storageBase   = '{{ asset("storage") }}/';
function openGallery(images) {
    galleryImages = images; galleryIdx = 0;
    renderGallery();
    document.getElementById('galleryModal').classList.remove('hidden');
}
function closeGallery() { document.getElementById('galleryModal').classList.add('hidden'); }
function renderGallery() {
    document.getElementById('galleryMainImg').src = storageBase + galleryImages[galleryIdx];
    document.getElementById('galleryCounter').textContent = (galleryIdx + 1) + ' / ' + galleryImages.length;
    var thumbs = document.getElementById('galleryThumbs');
    thumbs.innerHTML = '';
    galleryImages.forEach(function(img, i) {
        var t = document.createElement('img');
        t.src = storageBase + img;
        t.className = 'h-14 w-14 rounded-lg object-cover cursor-pointer border-2 transition shrink-0 ' + (i === galleryIdx ? 'border-white scale-105' : 'border-transparent opacity-50 hover:opacity-100');
        t.onclick = function() { galleryIdx = i; renderGallery(); };
        thumbs.appendChild(t);
    });
}
function galleryPrev() { if (galleryIdx > 0) { galleryIdx--; renderGallery(); } }
function galleryNext() { if (galleryIdx < galleryImages.length - 1) { galleryIdx++; renderGallery(); } }
document.addEventListener('keydown', function(e) {
    if (!document.getElementById('galleryModal').classList.contains('hidden')) {
        if (e.key === 'ArrowLeft') galleryPrev();
        if (e.key === 'ArrowRight') galleryNext();
        if (e.key === 'Escape') closeGallery();
    }
    if (!document.getElementById('feedbackModal').classList.contains('hidden') && e.key === 'Escape') closeFeedbackModal();
});
</script>
@endsection

