@extends('layouts.reos')

@section('title', 'Agreements & Legal Approvals - REOS')

@section('content')
<div class="space-y-8">
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Legal Agreements & Agreement Skip Approvals</h1>
            <p class="text-xs text-slate-600 mt-1 font-medium">Manage buyer agreements, agreement skip requests, and director execution approvals</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 bg-purple-50 border border-purple-200 px-3.5 py-2 rounded-2xl">
            <span class="text-purple-900">Total Agreements: {{ $agreements->count() }}</span>
        </div>
    </div>

    <!-- Active Agreements Table -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-lg font-black text-slate-900">Property Buyer Agreements Status</h2>
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-800 font-extrabold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-4">Customer Name</th>
                        <th class="p-4">Project & Unit</th>
                        <th class="p-4">Agreement Status</th>
                        <th class="p-4">Skip Request</th>
                        <th class="p-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($agreements as $ag)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-900">
                            {{ $ag->booking->customer_name ?? 'N/A' }}
                        </td>
                        <td class="p-4 text-xs font-bold text-slate-900">
                            <div><i class="fa-solid fa-building text-indigo-600 mr-1"></i>{{ $ag->booking->project->name ?? 'N/A' }}</div>
                            <span class="text-[10px] text-slate-500 font-mono">Unit {{ $ag->booking->unit->unit_number ?? 'N/A' }}</span>
                        </td>
                        <td class="p-4 text-xs">
                            <span class="px-3 py-1 font-bold rounded-full bg-emerald-100 text-emerald-900 border border-emerald-300 uppercase">
                                {{ $ag->status }}
                            </span>
                        </td>
                        <td class="p-4 text-xs">
                            @if($ag->skip_status === 'pending')
                                <span class="px-3 py-1 font-bold rounded-full bg-amber-100 text-amber-900 border border-amber-300">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-600 mr-1"></i>Skip Approval Pending
                                </span>
                            @elseif($ag->skip_status === 'approved')
                                <span class="px-3 py-1 font-bold rounded-full bg-emerald-100 text-emerald-900 border border-emerald-300">
                                    <i class="fa-solid fa-check text-emerald-600 mr-1"></i>Agreement Skipped
                                </span>
                            @else
                                <span class="px-3 py-1 font-bold rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                    Standard Process
                                </span>
                            @endif
                        </td>
                        <td class="p-4 flex flex-col space-y-2">
                            @if($ag->draft_file_path)
                                <a href="{{ route('agreements.file', [$ag->id, 'draft']) }}" target="_blank" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200 inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-file-pdf text-indigo-600 mr-1"></i><span>View Draft PDF</span>
                                </a>
                            @endif

                            @if($ag->signed_file_path)
                                <a href="{{ route('agreements.file', [$ag->id, 'signed']) }}" target="_blank" class="px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs rounded-xl border border-emerald-300 inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i><span>View Signed Agreement</span>
                                </a>
                            @endif

                            <button onclick="openUploadModal({{ $ag->id }}, '{{ $ag->agreement_number }}')" class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition flex items-center space-x-1">
                                <i class="fa-solid fa-upload mr-1"></i><span>Upload PDF Document</span>
                            </button>

                            @if($ag->skip_status === 'pending' && (auth()->user()->isCompanyAdmin() || auth()->user()->isSaaSFounder()))
                            <form method="POST" action="{{ route('agreements.approve-skip', $ag->id) }}" class="mt-1">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition">
                                    Approve Agreement Skip
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-500 font-medium text-xs">No agreements generated yet. Select a booking to generate a buyer agreement.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div id="uploadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white w-full max-w-md p-6 rounded-3xl space-y-4 border border-slate-200 shadow-2xl">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-black text-slate-900"><i class="fa-solid fa-upload mr-1"></i>Upload Agreement PDF</h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold hover:bg-slate-200">✕</button>
        </div>
        <form id="uploadForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block text-slate-700 mb-1 font-bold">Document Type</label>
                <select name="file_type" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-slate-900 focus:outline-none focus:border-indigo-600 font-bold">
                    <option value="draft">Draft Agreement (Initial Copy)</option>
                    <option value="signed">Signed Agreement (Executed Copy)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Select File (PDF / DOCX / Image, Max 10MB) *</label>
                <input type="file" name="agreement_file" required accept=".pdf,.doc,.docx,.jpg,.png" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 font-bold text-slate-900 focus:outline-none focus:border-indigo-600">
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-extrabold rounded-xl shadow-md transition">Upload File</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUploadModal(agreementId, agreementNumber) {
        document.getElementById('modalAgreementTitle').innerText = 'Agreement #' + agreementNumber;
        document.getElementById('uploadForm').action = '/agreements/' + agreementId + '/upload';
        document.getElementById('uploadModal').classList.remove('hidden');
    }
</script>
@endsection
