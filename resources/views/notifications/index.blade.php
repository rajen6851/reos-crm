@extends('layouts.reos')

@section('title', 'System Alerts & Notifications - REOS')

@section('content')
<div class="space-y-8">
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">System Alerts & Notifications Feed</h1>
            <p class="text-xs text-slate-600 mt-1 font-medium">Real-time alerts for lead assignments, site visit logs, and booking token receipts</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 bg-indigo-50 border border-indigo-200 px-3.5 py-2 rounded-2xl">
            <i class="fa-solid fa-bolt text-indigo-600 mr-1"></i><span>Live Notifications Active</span>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
        @forelse($notifications as $n)
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-4 hover:bg-white transition">
            <div class="flex items-start space-x-3">
                <div class="w-9 h-9 rounded-2xl bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center justify-center mt-0.5">
                    <i class="fa-solid fa-bell text-indigo-600"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-900 leading-snug">{{ $n->description }}</div>
                    <div class="text-[11px] text-slate-500 font-mono mt-1">
                        Triggered by <span class="font-bold text-slate-700">{{ $n->user->name ?? 'System' }}</span> for Lead <span class="font-bold text-indigo-600">{{ $n->lead->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="text-[11px] text-slate-400 font-mono whitespace-nowrap">
                {{ $n->created_at->diffForHumans() }}
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-slate-500 font-medium text-xs rounded-2xl bg-slate-50 border border-slate-200">
            No system notifications recorded yet.
        </div>
        @endforelse

        <div class="pt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
