@extends('layouts.reos')

@section('title', 'Leave Management – UrbanProperty')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Banner & Breadcrumb -->
    <div class="bg-gradient-to-r from-[#0F172A] to-[#1E293B] text-white p-6 rounded-2xl shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
        <div class="relative z-10">
            <div class="flex items-center space-x-2 text-xs font-semibold text-indigo-300 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-white transition">Home</a>
                <span>›</span>
                <a href="{{ route('hrms.dashboard') }}" class="hover:text-white transition">HRMS</a>
                <span>›</span>
                <span class="text-white font-bold">Leave Management</span>
            </div>
            <h1 class="page-heading text-3xl font-extrabold tracking-tight">
                Leave Management
            </h1>
            <p class="text-indigo-200 text-sm mt-1 max-w-xl">
                Track, approve, and manage staff leave requests seamlessly.
            </p>
        </div>
        
        @if(!auth()->user()->isSaaSFounder() && !auth()->user()->isCompanyFounder() && !auth()->user()->isCompanyAdmin())
        <div class="relative z-10 flex space-x-3">
            <button onclick="document.getElementById('leaveModal').classList.remove('hidden')" class="px-5 py-2.5 bg-white text-[#0F172A] hover:bg-indigo-50 font-bold rounded-xl shadow-lg transition flex items-center space-x-2">
                <i class="fa-solid fa-plus text-indigo-600"></i>
                <span>Apply for Leave</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Leaves Table -->
    <div class="bg-white/80 backdrop-blur-xl border border-slate-200 p-0 rounded-2xl shadow-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-white">
            <h3 class="text-lg font-extrabold text-slate-900"><i class="fa-solid fa-calendar-check text-indigo-500 mr-2"></i>Leave Requests Log</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wider font-extrabold">
                        <th class="py-4 px-6">Employee</th>
                        <th class="py-4 px-6">Leave Details</th>
                        <th class="py-4 px-6">Days</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leaveRequests as $lr)
                    <tr class="hover:bg-indigo-50/50 transition duration-200 group">
                        <td class="py-4 px-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-sm">
                                    {{ substr($lr->user->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-extrabold text-slate-900">{{ $lr->user->name ?? 'User' }}</div>
                                    @if(auth()->user()->isSaaSFounder())
                                        <div class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded-full inline-block mt-1">{{ $lr->company->name ?? '' }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="font-bold text-indigo-600 uppercase text-[10px] tracking-wide mb-1">{{ str_replace('_', ' ', $lr->leave_type) }}</div>
                            <div class="text-[11px] text-slate-600 font-semibold font-mono">{{ date('M j, Y', strtotime($lr->start_date)) }} — {{ date('M j, Y', strtotime($lr->end_date)) }}</div>
                            <div class="text-[10px] text-slate-400 mt-1 truncate max-w-[150px]" title="{{ $lr->reason }}">{{ $lr->reason }}</div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-700 font-mono font-bold">{{ $lr->total_days }}</span>
                        </td>
                        <td class="py-4 px-6">
                            @if($lr->status === 'approved')
                                <span class="px-3 py-1.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200 shadow-sm"><i class="fa-solid fa-check-circle mr-1"></i>Approved</span>
                            @elseif($lr->status === 'rejected')
                                <span class="px-3 py-1.5 text-xs font-bold rounded-full bg-rose-100 text-rose-700 border border-rose-200 shadow-sm"><i class="fa-solid fa-xmark-circle mr-1"></i>Rejected</span>
                            @elseif($lr->status === 'pending_deletion')
                                <span class="px-3 py-1.5 text-xs font-bold rounded-full bg-red-100 text-red-700 border border-red-200 shadow-sm animate-pulse"><i class="fa-solid fa-trash-can mr-1"></i>Deletion Pending</span>
                                <div class="text-[9px] text-red-500 font-bold mt-1">Requested by {{ $lr->deletionRequester->name ?? 'Admin' }}</div>
                            @else
                                <span class="px-3 py-1.5 text-xs font-bold rounded-full bg-amber-100 text-amber-700 border border-amber-200 shadow-sm"><i class="fa-solid fa-clock mr-1"></i>Pending</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                @if($lr->status === 'pending' && (auth()->user()->isCompanyAdmin() || auth()->user()->isManager() || auth()->user()->isSaaSFounder()))
                                <form action="{{ route('hrms.leave-requests.status', $lr->id) }}" method="POST" class="inline">
                                    @csrf <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-600 font-bold rounded-lg text-xs transition border border-emerald-200">Approve</button>
                                </form>
                                <form action="{{ route('hrms.leave-requests.status', $lr->id) }}" method="POST" class="inline">
                                    @csrf <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 font-bold rounded-lg text-xs transition border border-rose-200">Reject</button>
                                </form>
                                @endif
                                
                                <!-- Maker-Checker Deletion -->
                                @if($lr->status === 'pending_deletion' && (auth()->user()->isCompanyFounder() || auth()->user()->isSaaSFounder()))
                                    <form action="{{ route('hrms.leave-requests.approve-delete', $lr->id) }}" method="POST" class="inline">
                                        @csrf <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-xs transition shadow-sm">Confirm Delete</button>
                                    </form>
                                    <form action="{{ route('hrms.leave-requests.approve-delete', $lr->id) }}" method="POST" class="inline">
                                        @csrf <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition">Reject Delete</button>
                                    </form>
                                @elseif($lr->status !== 'pending_deletion' && (auth()->user()->isCompanyFounder() || auth()->user()->isSaaSFounder() || auth()->user()->isCompanyAdmin()))
                                    <form action="{{ route('hrms.leave-requests.delete', $lr->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this leave request?');">
                                        @csrf
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 hover:bg-red-600 text-red-500 hover:text-white transition">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center opacity-50">
                                <i class="fa-solid fa-umbrella-beach text-5xl text-slate-300 mb-4"></i>
                                <span class="text-slate-500 font-bold">No leave requests found.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
@if(!auth()->user()->isSaaSFounder())
<div id="leaveModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-extrabold text-slate-900">Apply for Leave</h3>
            <button onclick="document.getElementById('leaveModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form action="{{ route('hrms.leave-requests.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Leave Type</label>
                <select name="leave_type" required class="w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="casual_leave">Casual Leave</option>
                    <option value="sick_leave">Sick Leave</option>
                    <option value="earned_leave">Earned Leave</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" required class="w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">End Date</label>
                    <input type="date" name="end_date" required class="w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Reason</label>
                <textarea name="reason" rows="3" required class="w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Please mention the reason..."></textarea>
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition">Submit Request</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
