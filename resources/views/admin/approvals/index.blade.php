@extends('layouts.reos')

@section('title', 'SaaS Pending Approvals – REOS Platform')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <span>SaaS Governance</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-amber-700">Approval Queue</span>
            </div>
            <h1 class="page-heading">SaaS Approval Requests Queue</h1>
            <p class="body-text mt-1">Review and approve/reject critical platform actions requested by SaaS Sub-Admins.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="px-4 py-2 bg-amber-100 text-amber-900 border border-amber-300 rounded-xl font-extrabold text-xs flex items-center space-x-2">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>{{ $pendingRequests->count() }} Requests Pending Review</span>
            </span>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-xs font-bold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs font-bold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    <!-- Pending Requests Queue Table -->
    <div class="reos-card overflow-hidden border-2 border-amber-200">
        <div class="p-4 bg-amber-50/60 border-b border-amber-200 flex items-center justify-between">
            <h2 class="section-heading text-base flex items-center space-x-2 text-amber-900">
                <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                <span>Pending Approval Requests (Action Required)</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-extrabold uppercase text-slate-500 tracking-wider">
                        <th class="py-3 px-4">Requested Action</th>
                        <th class="py-3 px-4">Target Entity</th>
                        <th class="py-3 px-4">Requested By Sub-Admin</th>
                        <th class="py-3 px-4">Reason / Notes</th>
                        <th class="py-3 px-4 text-right">Founder Decision</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-800">
                    @forelse($pendingRequests as $req)
                        <tr class="hover:bg-amber-50/30 transition">
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold border {{ $req->action_badge }}">
                                    {{ $req->action_label }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-1 font-mono">Req #{{ $req->id }} • {{ $req->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900">{{ $req->target_name ?? 'N/A' }}</div>
                                <div class="text-[10px] text-slate-400">Target ID: #{{ $req->target_id ?? 'N/A' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-800">{{ $req->requestedBy->name ?? 'Sub-Admin' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $req->requestedBy->email ?? '' }}</div>
                            </td>
                            <td class="py-4 px-4 max-w-xs">
                                <p class="text-slate-600 italic bg-slate-50 p-2 rounded-lg border border-slate-200 text-[11px] leading-relaxed">
                                    "{{ $req->reason ?? 'No explanation provided.' }}"
                                </p>
                            </td>
                            <td class="py-4 px-4 text-right">
                                @if(auth()->user()->isSaaSFounder())
                                    <div class="flex items-center justify-end space-x-2">
                                        <button onclick="openApproveModal({{ json_encode($req) }})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition shadow-2xs flex items-center space-x-1 cursor-pointer">
                                            <i class="fa-solid fa-check"></i>
                                            <span>Approve</span>
                                        </button>
                                        <button onclick="openRejectModal({{ json_encode($req) }})" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs transition shadow-2xs flex items-center space-x-1 cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                            <span>Reject</span>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[11px] text-amber-700 font-bold bg-amber-50 px-2 py-1 rounded border border-amber-200">
                                        Waiting for SaaS Founder Review
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400 font-semibold">
                                <i class="fa-solid fa-circle-check text-4xl text-emerald-400 mb-2 block"></i>
                                Great! There are no pending approval requests at this moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Processed Requests History Table -->
    <div class="reos-card overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200">
            <h2 class="section-heading text-base flex items-center space-x-2">
                <i class="fa-solid fa-history text-slate-500"></i>
                <span>Approval Request Audit Log & History</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-extrabold uppercase text-slate-500 tracking-wider">
                        <th class="py-3 px-4">Action & Target</th>
                        <th class="py-3 px-4">Requested By</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Reviewed By & Notes</th>
                        <th class="py-3 px-4 text-right">Processed At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-800">
                    @forelse($historicalRequests as $req)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $req->action_label }}</div>
                                <div class="text-[11px] text-slate-500">{{ $req->target_name }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800">{{ $req->requestedBy->name ?? 'Sub-Admin' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($req->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        Approved & Executed
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300">
                                        Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-800 font-semibold">{{ $req->reviewedBy->name ?? 'SaaS Founder' }}</div>
                                <div class="text-[11px] text-slate-500 italic">"{{ $req->reviewer_notes ?? 'No notes' }}"</div>
                            </td>
                            <td class="py-3.5 px-4 text-right text-slate-500 text-[11px]">
                                {{ $req->updated_at->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 font-semibold">
                                No historical approval request logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Approve Confirmation -->
<div id="approveModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-xl overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-emerald-50 flex items-center justify-between">
            <h3 class="font-extrabold text-emerald-900 text-base flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>Confirm SaaS Action Approval</span>
            </h3>
            <button onclick="document.getElementById('approveModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="approveForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            <p class="text-xs text-slate-600 leading-relaxed">
                You are approving request <strong id="approve_action_title" class="text-slate-900"></strong>. Approving will automatically execute this action on the platform immediately.
            </p>

            <div>
                <label class="form-label">Reviewer Notes (Optional)</label>
                <textarea name="reviewer_notes" rows="2" placeholder="e.g. Verified request details. Approved." class="form-input"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 border-t border-slate-200 pt-4">
                <button type="button" onclick="document.getElementById('approveModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-200 transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center space-x-1">
                    <i class="fa-solid fa-check"></i>
                    <span>Confirm & Execute Action</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reject Confirmation -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-xl overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-rose-50 flex items-center justify-between">
            <h3 class="font-extrabold text-rose-900 text-base flex items-center space-x-2">
                <i class="fa-solid fa-circle-xmark text-rose-600"></i>
                <span>Reject Approval Request</span>
            </h3>
            <button onclick="document.getElementById('rejectModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="rejectForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            <p class="text-xs text-slate-600 leading-relaxed">
                You are rejecting request <strong id="reject_action_title" class="text-slate-900"></strong>. The sub-admin will be notified of your decision.
            </p>

            <div>
                <label class="form-label">Reason for Rejection <span class="text-rose-500">*</span></label>
                <textarea name="reviewer_notes" required rows="3" placeholder="Explain why this request is rejected..." class="form-input"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 border-t border-slate-200 pt-4">
                <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-200 transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center space-x-1">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Reject Request</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openApproveModal(req) {
        document.getElementById('approveForm').action = `/admin/saas-approvals/${req.id}/approve`;
        document.getElementById('approve_action_title').innerText = `#${req.id}: ${req.action_label} (${req.target_name || ''})`;
        document.getElementById('approveModal').classList.remove('hidden');
    }

    function openRejectModal(req) {
        document.getElementById('rejectForm').action = `/admin/saas-approvals/${req.id}/reject`;
        document.getElementById('reject_action_title').innerText = `#${req.id}: ${req.action_label} (${req.target_name || ''})`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }
</script>
@endsection
