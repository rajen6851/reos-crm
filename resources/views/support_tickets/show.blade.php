@extends('layouts.reos')

@section('title', 'Ticket #' . $ticket->ticket_number . ' - REOS Helpdesk')

@section('content')
<div class="space-y-8">

    <!-- Top Navigation Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('support-tickets.index') }}" class="text-xs font-black text-indigo-600 hover:text-indigo-800 flex items-center space-x-1">
            <span>←</span>
            <span>Back to Support Desk</span>
        </a>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-slate-500 font-bold">Ticket ID:</span>
            <span class="font-mono font-black text-xs text-indigo-700 bg-indigo-50 border border-indigo-200 px-3 py-1 rounded-full">
                #{{ $ticket->ticket_number }}
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold flex items-center space-x-2">
        <i class="fa-solid fa-circle-check text-emerald-600"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Main Ticket Description & Conversation Thread -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Initial Ticket Banner -->
            <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md bg-slate-100 border border-slate-200 text-slate-700">
                            <i class="fa-solid fa-folder text-slate-500 mr-1"></i>{{ $ticket->category }}
                        </span>
                        <h1 class="text-xl font-black text-slate-900 mt-2">{{ $ticket->subject }}</h1>
                    </div>
                    @php
                        $sBadge = match($ticket->status) {
                            'open' => 'bg-amber-100 text-amber-900 border-amber-300',
                            'in_progress' => 'bg-indigo-100 text-indigo-900 border-indigo-300',
                            'resolved' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                            'closed' => 'bg-slate-200 text-slate-800 border-slate-400',
                        };
                    @endphp
                    <span class="px-3.5 py-1 rounded-full text-xs font-extrabold border {{ $sBadge }}">
                        ● {{ strtoupper(str_replace('_', ' ', $ticket->status)) }}
                    </span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-sm text-slate-800 leading-relaxed font-medium whitespace-pre-line">
                    {{ $ticket->description }}
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">
                    <div>Raised by: <strong>{{ $ticket->user->name ?? 'User' }}</strong> ({{ $ticket->user->email ?? '' }})</div>
                    <div>Created: {{ $ticket->created_at ? $ticket->created_at->format('d M Y, h:i A') : 'N/A' }}</div>
                </div>
            </div>

            <!-- Replies & Discussion Thread -->
            <div class="space-y-4">
                <h2 class="text-lg font-black text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-comments text-indigo-600"></i>
                    <span>Ticket Conversation Timeline</span>
                </h2>

                @forelse($ticket->replies as $r)
                <div class="p-6 rounded-3xl bg-white border {{ $r->is_internal_note ? 'border-amber-300 bg-amber-50/40' : 'border-slate-200' }} shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-black text-xs flex items-center justify-center">
                                {{ strtoupper(substr($r->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-xs font-black text-slate-900">
                                    {{ $r->user->name ?? 'User' }}
                                    @if($r->is_internal_note)
                                    <span class="ml-2 text-[10px] bg-amber-200 text-amber-900 px-2 py-0.5 rounded-md uppercase font-black">Internal Note</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $r->created_at ? $r->created_at->format('d M Y, h:i A') : '' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-xs text-slate-800 leading-relaxed font-medium whitespace-pre-line pl-10">
                        {{ $r->message }}
                    </div>
                </div>
                @empty
                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-500 font-medium">
                    No replies posted yet. Use the reply box below to communicate.
                </div>
                @endforelse
            </div>

            <!-- Reply Box Form -->
            <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-black text-slate-900">Post Reply or Resolution Note</h3>

                <form action="{{ route('support-tickets.reply', $ticket->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <textarea name="message" rows="4" required placeholder="Type your response here..." class="w-full rounded-2xl border-slate-200 text-xs font-medium focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    @if(auth()->user()->isManager() || auth()->user()->isCompanyAdmin())
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="is_internal_note" id="internal_note" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <label for="internal_note" class="text-xs font-bold text-slate-700">Internal Support Note (Visible only to team admins/managers)</label>
                    </div>
                    @endif

                    <div class="flex items-center justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-md shadow-indigo-500/20">
                            Post Response
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right 1 Col: Ticket Admin Controls & Details -->
        <div class="space-y-6">

            <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-6">
                <h3 class="text-sm font-black text-slate-900 border-b border-slate-100 pb-3">Ticket Metadata</h3>

                <div class="space-y-4 text-xs">
                    <div>
                        <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px]">Priority Level</span>
                        @php
                            $pBadge = match($ticket->priority) {
                                'urgent' => 'bg-rose-100 text-rose-900 border-rose-300',
                                'high' => 'bg-amber-100 text-amber-900 border-amber-300',
                                'medium' => 'bg-sky-100 text-sky-900 border-sky-300',
                                default => 'bg-slate-100 text-slate-800 border-slate-300'
                            };
                        @endphp
                        <span class="inline-block mt-1 px-3 py-1 rounded-full font-bold border {{ $pBadge }}">
                            {{ strtoupper($ticket->priority) }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px]">Assigned Agent</span>
                        <div class="font-bold text-slate-900 mt-1">
                            <i class="fa-solid fa-user-tie text-slate-500 mr-1"></i>{{ $ticket->assignedTo->name ?? 'Unassigned' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px]">Tenant Company</span>
                        <div class="font-bold text-slate-900 mt-1">
                            <i class="fa-solid fa-building text-indigo-600 mr-1"></i>{{ $ticket->company->name ?? 'N/A' }}
                        </div>
                    </div>
                </div>

                <!-- Admin Status Update Control Form -->
                @if(auth()->user()->isManager() || auth()->user()->isCompanyAdmin())
                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">Update Status / Assign Agent</h4>

                    <form action="{{ route('support-tickets.update-status', $ticket->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase text-slate-500 mb-1">Status</label>
                            <select name="status" class="w-full rounded-2xl border-slate-200 text-xs font-bold focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-extrabold uppercase text-slate-500 mb-1">Assignee</label>
                            <select name="assigned_to_user_id" class="w-full rounded-2xl border-slate-200 text-xs font-bold focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Select Support Staff --</option>
                                @foreach($agents as $ag)
                                <option value="{{ $ag->id }}" {{ $ticket->assigned_to_user_id == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-2xl bg-slate-900 hover:bg-black text-white font-black text-xs transition">
                            Update Ticket
                        </button>
                    </form>
                </div>
                @endif

            </div>

        </div>

    </div>

</div>
@endsection
