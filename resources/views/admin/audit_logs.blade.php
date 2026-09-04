@extends('layouts.reos')

@section('title', 'Enterprise Audit Trail & Activity Logs - REOS')

@section('content')
<div class="space-y-8">
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-shield-halved text-2xl text-indigo-600"></i>
                <h1 class="text-2xl font-black text-slate-900">Enterprise Audit Trail & System Logs</h1>
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium">Real-time security log, user activity history, system events, and state mutations</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-600 bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-2xl">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Live System Logging Active</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by user, description or event..." class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3.5 py-2.5 text-slate-900 font-bold w-full md:w-64 focus:outline-none focus:border-indigo-600">
                
                <select name="event" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3.5 py-2.5 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                    <option value="">All Event Categories</option>
                    <option value="lead" {{ request('event') == 'lead' ? 'selected' : '' }}>Lead Events</option>
                    <option value="booking" {{ request('event') == 'booking' ? 'selected' : '' }}>Booking & Payments</option>
                    <option value="unit" {{ request('event') == 'unit' ? 'selected' : '' }}>Unit Inventory</option>
                    <option value="user" {{ request('event') == 'user' ? 'selected' : '' }}>User Management</option>
                    <option value="subscription" {{ request('event') == 'subscription' ? 'selected' : '' }}>SaaS Subscription</option>
                </select>

                <select name="user_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3.5 py-2.5 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                    <option value="">All Team Members</option>
                    @foreach($teamUsers as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role->name ?? 'User' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl transition shadow-xs">Filter Logs</button>
                <a href="{{ route('activity-logs.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-200">Reset</a>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
        @if($auditLogs->isEmpty())
            <div class="p-8 text-center text-slate-600 font-medium text-xs rounded-2xl bg-slate-50 border border-slate-200">No activity audit logs recorded for the selected filter criteria.</div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="text-xs uppercase bg-slate-50 text-slate-800 font-extrabold tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="p-4 rounded-l-xl">Timestamp</th>
                            <th class="p-4">User / Actor</th>
                            <th class="p-4">Event Type</th>
                            <th class="p-4">Description</th>
                            <th class="p-4">IP Address</th>
                            <th class="p-4 rounded-r-xl">Changes Payload</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($auditLogs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 text-xs font-mono font-bold text-slate-900">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                                <div class="text-[10px] text-slate-500 font-normal font-sans">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="p-4 text-xs font-bold text-slate-900">
                                {{ $log->user_name ?? 'System' }}
                                <div class="text-[10px] text-indigo-700 font-mono font-semibold">{{ $log->user_role ?? 'System' }}</div>
                            </td>
                            <td class="p-4">
                                @php
                                    $badgeStyle = match(true) {
                                        str_contains($log->event, 'lead') => 'bg-amber-100 text-amber-900 border-amber-300',
                                        str_contains($log->event, 'booking') => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                                        str_contains($log->event, 'unit') => 'bg-indigo-100 text-indigo-900 border-indigo-300',
                                        str_contains($log->event, 'user') => 'bg-purple-100 text-purple-900 border-purple-300',
                                        default => 'bg-slate-100 text-slate-900 border-slate-300'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-black rounded-lg border uppercase tracking-wider {{ $badgeStyle }}">
                                    {{ str_replace('_', ' ', $log->event) }}
                                </span>
                            </td>
                            <td class="p-4 text-xs font-medium text-slate-900 max-w-xs">
                                {{ $log->description }}
                            </td>
                            <td class="p-4 text-xs font-mono font-bold text-slate-600">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                            <td class="p-4">
                                @if($log->old_values || $log->new_values)
                                    <button onclick="openPayloadModal({{ json_encode($log) }})" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-indigo-900 font-bold text-[11px] rounded-lg border border-slate-300 transition flex items-center space-x-1">
                                        <i class="fa-solid fa-magnifying-glass text-indigo-600 mr-1"></i><span>View Payload Diff</span>
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400 font-medium">— No Mutation Payload</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>

    <!-- Payload Diff Modal -->
    <div id="payloadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div class="bg-white w-full max-w-xl p-6 rounded-3xl space-y-4 border border-slate-200 shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Audit Trail Payload Data Diff</h3>
                    <p id="modal_event_title" class="text-xs text-amber-900 font-mono font-bold"></p>
                </div>
                <button onclick="document.getElementById('payloadModal').classList.add('hidden')" class="text-slate-500 hover:text-slate-900 font-bold text-lg">✕</button>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <h4 class="font-extrabold text-slate-700 mb-1">Original Values (Before)</h4>
                    <pre id="modal_old_values" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-[11px] font-mono text-slate-800 overflow-x-auto max-h-60"></pre>
                </div>
                <div>
                    <h4 class="font-extrabold text-slate-700 mb-1">Updated Values (After)</h4>
                    <pre id="modal_new_values" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-[11px] font-mono text-emerald-950 font-bold overflow-x-auto max-h-60"></pre>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" onclick="document.getElementById('payloadModal').classList.add('hidden')" class="px-5 py-2 BG-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl border border-slate-200 text-xs">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openPayloadModal(log) {
        document.getElementById('modal_event_title').innerText = log.event + ' (' + (log.user_name || 'System') + ')';
        document.getElementById('modal_old_values').innerText = log.old_values ? JSON.stringify(log.old_values, null, 2) : 'None / Initial Record';
        document.getElementById('modal_new_values').innerText = log.new_values ? JSON.stringify(log.new_values, null, 2) : 'None / Action Only';
        document.getElementById('payloadModal').classList.remove('hidden');
    }
</script>
@endsection
