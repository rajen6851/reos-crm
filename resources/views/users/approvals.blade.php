@extends('layouts.reos')

@section('title', 'Critical Approvals Queue â€“ UrbanProperty')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Header Banner -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-[#E2E8F0] shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>â€º</span>
                <span class="text-[#0F172A] font-bold">Critical Approvals</span>
            </div>
            <h1 class="page-heading text-2xl font-extrabold text-[#0F172A]">Main Owner Critical Approvals Queue</h1>
            <p class="body-text text-xs text-[#64748B] mt-0.5">Review, verify, and authorize high-priority operational requests submitted by company admins</p>
        </div>

        <div class="flex items-center space-x-2">
            <span class="px-3.5 py-1.5 bg-amber-50 text-amber-900 border border-amber-200 text-xs font-extrabold rounded-xl flex items-center space-x-2">
                <i class="fa-solid fa-shield-halved text-amber-600"></i>
                <span>Director Security Scope</span>
            </span>
        </div>
    </div>

    <!-- Pending Requests Container -->
    <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] flex justify-between items-center bg-slate-50/50">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
                <h3 class="section-heading text-base">Pending Authorization Requests</h3>
            </div>
            <span class="px-2.5 py-1 bg-amber-100 text-amber-900 text-xs font-bold rounded-full border border-amber-200">
                {{ $pendingApprovals->count() }} Pending
            </span>
        </div>

        @if($pendingApprovals->isEmpty())
            <div class="p-12 text-center text-slate-500 font-medium text-xs space-y-2">
                <div class="w-12 h-12 rounded-full bg-emerald-50 text-[#059669] mx-auto flex items-center justify-center text-xl border border-emerald-200 mb-3">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="text-slate-900 font-bold text-sm">No Pending Approvals</div>
                <p class="text-slate-500 max-w-sm mx-auto">All operational requests from company admins have been reviewed and executed.</p>
            </div>
        @else
            <div class="divide-y divide-[#E2E8F0]">
                @foreach($pendingApprovals as $approval)
                <div class="p-6 hover:bg-slate-50/80 transition flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg border {{ $approval->action_badge }}">
                                {{ $approval->action_label }}
                            </span>
                            <span class="text-xs text-slate-400 font-mono">Request #{{ $approval->id }}</span>
                        </div>

                        <div>
                            <div class="text-sm font-extrabold text-[#0F172A]">
                                Target: <span class="text-[#DC2626] font-mono">{{ $approval->target_name }}</span>
                            </div>
                            <div class="text-xs text-[#64748B] mt-1 flex items-center space-x-2">
                                <span>Requested by: <strong class="text-slate-800">{{ $approval->requestedBy->name ?? 'Admin User' }}</strong></span>
                                <span>â€¢</span>
                                <span class="font-mono text-slate-500">{{ $approval->created_at->format('d M Y, h:i A') }} ({{ $approval->created_at->diffForHumans() }})</span>
                            </div>
                        </div>

                        @if($approval->reason)
                            <div class="text-xs text-amber-900 bg-amber-50 rounded-xl p-3 border border-amber-200 max-w-2xl">
                                ðŸ’¬ <strong>Reason:</strong> "{{ $approval->reason }}"
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center space-x-3 shrink-0 self-end md:self-center">
                        <form action="{{ route('users.approvals.approve', $approval->id) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Are you sure you want to APPROVE & EXECUTE this action?')" class="px-5 py-2.5 bg-[#059669] hover:bg-[#047857] text-white text-xs font-bold rounded-xl shadow-2xs transition flex items-center space-x-2 cursor-pointer">
                                <i class="fa-solid fa-check text-xs"></i>
                                <span>Approve & Execute</span>
                            </button>
                        </form>

                        <form action="{{ route('users.approvals.reject', $approval->id) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Are you sure you want to REJECT this request?')" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-2xs transition flex items-center space-x-2 cursor-pointer">
                                <i class="fa-solid fa-xmark text-xs"></i>
                                <span>Reject Request</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Processed Approvals History -->
    @if(isset($processedApprovals) && $processedApprovals->count() > 0)
    <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] flex justify-between items-center bg-slate-50/50">
            <h3 class="section-heading text-base">Recently Processed Approvals History</h3>
            <span class="text-xs text-slate-500 font-medium">Last {{ $processedApprovals->count() }} Decisions</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-[#64748B] font-bold border-b border-[#E2E8F0] uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="p-4">Action Type</th>
                        <th class="p-4">Target Name</th>
                        <th class="p-4">Requested By</th>
                        <th class="p-4">Decision</th>
                        <th class="p-4">Reviewed By</th>
                        <th class="p-4 font-mono">Processed Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] font-medium">
                    @foreach($processedApprovals as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-4">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded border {{ $p->action_badge }}">
                                {{ $p->action_label }}
                            </span>
                        </td>
                        <td class="p-4 font-bold text-slate-900 font-mono">{{ $p->target_name }}</td>
                        <td class="p-4">{{ $p->requestedBy->name ?? 'Admin' }}</td>
                        <td class="p-4">
                            @if($p->status === 'approved')
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-lg border border-emerald-300">
                                    âœ“ Approved
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-100 text-rose-800 font-bold rounded-lg border border-rose-300">
                                    âœ• Rejected
                                </span>
                            @endif
                        </td>
                        <td class="p-4">{{ $p->reviewedBy->name ?? 'Director' }}</td>
                        <td class="p-4 font-mono text-slate-500">{{ $p->updated_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
