@extends('layouts.reos')

@section('title', 'Team Activity Log - REOS')

@section('content')
<div class="space-y-8">
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-list-check text-2xl text-indigo-600"></i>
                <h1 class="text-2xl font-black text-slate-900">Team Activity Log & Work Feed</h1>
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium">Monitor sales call feedback, site visits conducted, lead assignments, and customer interactions</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-2xl">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            <span>Real-time Work Activity Feed</span>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer, lead code or remarks..." class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3.5 py-2.5 text-slate-900 font-bold w-full md:w-64 focus:outline-none focus:border-indigo-600">
                
                <select name="activity_type" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3.5 py-2.5 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                    <option value="">All Activity Types</option>
                    <option value="call_logged" {{ request('activity_type') == 'call_logged' ? 'selected' : '' }}>Call / Site Visit Logged</option>
                    <option value="status_updated" {{ request('activity_type') == 'status_updated' ? 'selected' : '' }}>Lead Status Changed</option>
                    <option value="lead_assigned" {{ request('activity_type') == 'lead_assigned' ? 'selected' : '' }}>Lead Assignment</option>
                    <option value="booking_created" {{ request('activity_type') == 'booking_created' ? 'selected' : '' }}>Booking & Unit Locks</option>
                </select>

                @if(!auth()->user()->isSales())
                <select name="user_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3.5 py-2.5 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                    <option value="">All Executive Staff</option>
                    @foreach($teamUsers as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role->name ?? 'User' }})</option>
                    @endforeach
                </select>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl transition shadow-xs">Filter Activities</button>
                <a href="{{ route('activity-logs.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-200">Reset</a>
            </div>
        </form>
    </div>

    <!-- Activity Log List -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
        @if($activities->isEmpty())
            <div class="p-8 text-center text-slate-600 font-medium text-xs rounded-2xl bg-slate-50 border border-slate-200">No work activities recorded for the selected filter.</div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="text-xs uppercase bg-slate-50 text-slate-800 font-extrabold tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="p-4 rounded-l-xl">Date & Time</th>
                            <th class="p-4">Staff Member</th>
                            <th class="p-4">Customer Lead</th>
                            <th class="p-4">Activity Category</th>
                            <th class="p-4 rounded-r-xl">Activity Description & Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($activities as $act)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 text-xs font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $act->created_at->format('d M Y, h:i A') }}
                                <div class="text-[10px] text-slate-500 font-normal font-sans">{{ $act->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="p-4 text-xs font-bold text-slate-900 whitespace-nowrap">
                                {{ $act->user->name ?? 'System' }}
                                <div class="text-[10px] text-indigo-700 font-bold">{{ $act->user->role->name ?? 'Staff' }}</div>
                            </td>
                            <td class="p-4 text-xs font-bold text-slate-900 whitespace-nowrap">
                                @if($act->lead)
                                    <div>{{ $act->lead->first_name }} {{ $act->lead->last_name }}</div>
                                    <div class="text-[11px] font-mono text-amber-800">{{ $act->lead->lead_code }}</div>
                                @else
                                    <span class="text-slate-400 font-normal">General Activity</span>
                                @endif
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                @php
                                    $badge = match($act->activity_type) {
                                        'call_logged' => ['bg' => 'bg-indigo-100 text-indigo-900 border-indigo-300', 'label' => 'Call / Visit Outcome'],
                                        'status_updated' => ['bg' => 'bg-amber-100 text-amber-900 border-amber-300', 'label' => 'Status Changed'],
                                        'lead_assigned' => ['bg' => 'bg-purple-100 text-purple-900 border-purple-300', 'label' => 'Lead Assigned'],
                                        'booking_created' => ['bg' => 'bg-emerald-100 text-emerald-900 border-emerald-300', 'label' => 'Booking / Unit Lock'],
                                        default => ['bg' => 'bg-slate-100 text-slate-900 border-slate-300', 'label' => strtoupper(str_replace('_', ' ', $act->activity_type))]
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-black rounded-lg border uppercase tracking-wider {{ $badge['bg'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="p-4 text-xs font-semibold text-slate-900">
                                {{ $act->description }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
