@extends('layouts.reos')

@section('title', 'Legal Agreements & Skip Approvals – REOS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ searchQuery: '', statusFilter: 'all' }">
    <!-- Header Banner -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-[#E2E8F0] shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Legal Agreements</span>
            </div>
            <h1 class="page-heading text-2xl font-extrabold text-[#0F172A]">Buyer Agreements & Director Skip Approvals</h1>
            <p class="body-text text-xs text-[#64748B] mt-0.5">Manage legal property agreements, draft execution files, signed buyer uploads, and director approval overrides</p>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <span class="px-4 py-2 bg-purple-50 text-purple-900 border border-purple-200 text-xs font-extrabold rounded-2xl flex items-center space-x-2">
                <i class="fa-solid fa-file-contract text-purple-600"></i>
                <span>{{ $agreements->count() }} Total Agreements</span>
            </span>
        </div>
    </div>

    <!-- Summary Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Total Agreements</span>
                <div class="text-2xl font-extrabold text-[#0F172A] mt-1 font-mono">{{ $agreements->count() }} Docs</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-[#4F46E5] flex items-center justify-center text-lg border border-indigo-100"><i class="fa-solid fa-file-signature text-[#4F46E5]"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Signed & Executed</span>
                <div class="text-2xl font-extrabold text-[#059669] mt-1 font-mono">{{ $agreements->filter(fn($a) => !empty($a->signed_file_path))->count() }} Signed</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#059669] flex items-center justify-center text-lg border border-emerald-200"><i class="fa-solid fa-file-circle-check text-[#059669]"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Agreement Skips Pending</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-1 font-mono">{{ $agreements->filter(fn($a) => $a->skip_status === 'pending')->count() }} Approvals</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg border border-amber-200"><i class="fa-solid fa-user-shield text-amber-600"></i></span>
        </div>
    </div>

    <!-- Active Agreements Table -->
    <div class="bg-white rounded-3xl p-6 border border-[#E2E8F0] shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-[#0F172A]">Property Buyer Agreements Directory</h2>

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
                        <th class="p-3.5">Customer Name</th>
                        <th class="p-3.5">Project & Unit</th>
                        <th class="p-3.5">Agreement Status</th>
                        <th class="p-3.5">Skip Request</th>
                        <th class="p-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($agreements as $ag)
                    <tr x-show="searchQuery === '' || '{{ strtolower($ag->booking->customer_name ?? '') }}'.includes(searchQuery.toLowerCase())" class="hover:bg-slate-50/80 transition">
                        <td class="p-3.5 font-bold text-[#0F172A]">
                            {{ $ag->booking->customer_name ?? 'N/A' }}
                            <div class="text-[11px] text-[#64748B] font-mono">{{ $ag->agreement_number ?? 'AGR-PENDING' }}</div>
                        </td>
                        <td class="p-3.5">
                            <div class="font-bold text-[#0F172A]"><i class="fa-solid fa-building text-[#4F46E5] mr-1"></i>{{ $ag->booking->project->name ?? 'N/A' }}</div>
                            <span class="text-[11px] text-[#64748B] font-mono">Unit {{ $ag->booking->unit->unit_number ?? 'N/A' }}</span>
                        </td>
                        <td class="p-3.5">
                            <span class="px-2.5 py-1 font-bold rounded-full bg-emerald-50 text-[#059669] border border-emerald-200 uppercase text-[10px]">
                                {{ $ag->status }}
                            </span>
                        </td>
                        <td class="p-3.5">
                            @if($ag->skip_status === 'pending')
                                <span class="px-2.5 py-1 font-bold rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-[10px]">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-600 mr-1"></i>Skip Approval Pending
                                </span>
                            @elseif($ag->skip_status === 'approved')
                                <span class="px-2.5 py-1 font-bold rounded-full bg-emerald-50 text-[#059669] border border-emerald-200 text-[10px]">
                                    <i class="fa-solid fa-check text-[#059669] mr-1"></i>Agreement Skipped
                                </span>
                            @else
                                <span class="px-2.5 py-1 font-bold rounded-full bg-slate-50 text-slate-700 border border-slate-200 text-[10px]">
                                    Standard Process
                                </span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            <div class="flex items-center justify-end space-x-2">
                                @if($ag->draft_file_path)
                                    <a href="{{ route('agreements.file', [$ag->id, 'draft']) }}" target="_blank" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-[#4F46E5] font-bold text-xs rounded-xl border border-indigo-200 inline-flex items-center space-x-1">
                                        <i class="fa-solid fa-file-pdf text-[#4F46E5] mr-1"></i><span>Draft PDF</span>
                                    </a>
                                @endif

                                @if($ag->signed_file_path)
                                    <a href="{{ route('agreements.file', [$ag->id, 'signed']) }}" target="_blank" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-[#059669] font-bold text-xs rounded-xl border border-emerald-200 inline-flex items-center space-x-1">
                                        <i class="fa-solid fa-circle-check text-[#059669] mr-1"></i><span>Signed Copy</span>
                                    </a>
                                @endif

                                <button onclick="openUploadModal({{ $ag->id }}, '{{ $ag->agreement_number }}')" class="px-3 py-1.5 bg-[#0F172A] hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center space-x-1 cursor-pointer">
                                    <i class="fa-solid fa-upload mr-1 text-xs"></i><span>Upload PDF</span>
                                </button>

                                @if($ag->skip_status === 'pending' && auth()->user()->isDirectorOrFounder())
                                <form method="POST" action="{{ route('agreements.approve-skip', $ag->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                                        Director Approve Skip
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-[#64748B] font-medium text-xs">No agreements generated yet. Select a booking to generate a buyer agreement.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div id="uploadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white w-full max-w-md p-6 rounded-3xl space-y-4 border border-[#E2E8F0] shadow-2xl">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-base font-extrabold text-[#0F172A]">Upload Legal Agreement PDF</h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-[#0F172A] font-bold hover:bg-slate-200 flex items-center justify-center">✕</button>
        </div>

        <form id="uploadForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-[#475569] mb-1">Agreement File Type *</label>
                <select name="type" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-bold focus:outline-none focus:border-[#4F46E5]">
                    <option value="signed">Signed Executed Agreement (Buyer & Company Signed)</option>
                    <option value="draft">Revised Agreement Draft PDF</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-[#475569] mb-1">Select Agreement File (PDF format) *</label>
                <input type="file" name="agreement_file" accept=".pdf" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2 text-[#0F172A] font-medium">
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-[#0F172A] font-bold rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white font-bold rounded-xl shadow-xs">Upload Document</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUploadModal(id, agreementNumber) {
        document.getElementById('uploadForm').action = "/agreements/" + id + "/upload";
        document.getElementById('uploadModal').classList.remove('hidden');
    }
</script>
@endsection
