@extends('layouts.reos')

@section('title', 'Company Private Digital Drive & File Vault â€“ UrbanProperty Enterprise')

@section('content')
{{--
|--------------------------------------------------------------------------
| PREVIOUS INTER-ENTITY SHARING VIEW (COMMENTED OUT AS PER USER REQUEST)
|--------------------------------------------------------------------------
|
| <div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ activeTab: 'all' }">
|     <div class="bg-white rounded-3xl p-6 md:p-8 border border-[#E2E8F0] shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
|         <div class="space-y-1">
|             <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-[#4F46E5] uppercase tracking-wider border border-indigo-100">
|                 <i class="fa-solid fa-folder-open text-[#4F46E5]"></i>
|                 <span>Enterprise Document Management Vault</span>
|             </div>
|             <h1 class="page-heading text-2xl font-extrabold text-[#0F172A] tracking-tight">KYC & Digital File Repository</h1>
|             <p class="body-text text-xs text-[#64748B]">Organized identity verification, RERA licenses, partnership deeds, customer PAN/Aadhar cards, and employee records.</p>
|         </div>
|         <button onclick="document.getElementById('uploadKycModal').classList.remove('hidden')" class="px-5 py-3 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
|             <i class="fa-solid fa-cloud-arrow-up text-white text-sm"></i>
|             <span>+ Upload KYC Document</span>
|         </button>
|     </div>
|     <!-- Shared entity tables for Customers, Brokers, Employees... -->
| </div>
|
--}}

<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ activeCategory: 'all', activeConfidentiality: 'all', searchKeyword: '' }">
    <!-- Header Banner -->
    <div class="reos-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#2563EB]">Home</a>
                <span>â€º</span>
                <span class="text-[#0F172A] font-bold">Document Repository</span>
            </div>
            <h1 class="page-heading text-2xl font-extrabold text-slate-900">Company Digital File Repository</h1>
            <p class="body-text text-xs text-slate-500 mt-0.5">Secure internal drive for land title deeds, RERA approvals, GST filings, balance sheets, and project collaterals.</p>
        </div>

        <div class="flex items-center space-x-3 shrink-0">
            <button onclick="document.getElementById('uploadCompanyDriveModal').classList.remove('hidden')" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white btn-text text-xs rounded-lg shadow-xs transition flex items-center space-x-2 cursor-pointer font-semibold">
                <i class="fa-solid fa-cloud-arrow-up text-white text-xs"></i>
                <span>+ Upload Company File</span>
            </button>
        </div>
    </div>

    <!-- Alert Status Messages -->
    @if(session('status'))
        <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center space-x-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center space-x-2">
            <i class="fa-solid fa-circle-xmark text-rose-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Drive Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
        <div class="reos-card p-4 flex justify-between items-center">
            <div>
                <span class="text-slate-500 font-medium">Total Storage Files</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1 font-mono">{{ $totalFilesCount }} Files</div>
            </div>
            <span class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg border border-indigo-100"><i class="fa-solid fa-folder-tree"></i></span>
        </div>

        <div class="reos-card p-4 flex justify-between items-center">
            <div>
                <span class="text-slate-500 font-medium">Legal & RERA Documents</span>
                <div class="text-2xl font-extrabold text-emerald-700 mt-1 font-mono">{{ $legalFilesCount }} Files</div>
            </div>
            <span class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl border border-emerald-100"><i class="fa-solid fa-scale-balanced"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-2xs flex justify-between items-center">
            <div>
                <span class="text-slate-500 font-medium">Tax & Financial Assets</span>
                <div class="text-2xl font-extrabold text-sky-700 mt-1 font-mono">{{ $financialFilesCount }} Files</div>
            </div>
            <span class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl border border-sky-100"><i class="fa-solid fa-file-invoice-dollar"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-2xs flex justify-between items-center">
            <div>
                <span class="text-slate-500 font-medium">Confidential Vault Files</span>
                <div class="text-2xl font-extrabold text-rose-700 mt-1 font-mono">{{ $confidentialFilesCount }} Files</div>
            </div>
            <span class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl border border-rose-100"><i class="fa-solid fa-lock"></i></span>
        </div>
    </div>

    <!-- Category / Folder Navigation Bar -->
    <div class="bg-white p-3 rounded-3xl border border-slate-200/80 shadow-2xs space-y-3">
        <div class="flex items-center justify-between px-2">
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-1.5">
                <i class="fa-solid fa-folder text-indigo-600"></i>
                <span>Company Drive Folders & Categories</span>
            </span>
            <span class="text-[11px] text-slate-400 font-medium">Private Tenant Storage</span>
        </div>

        <div class="flex items-center space-x-2 overflow-x-auto text-xs font-semibold pb-1 scrollbar-none">
            <button @click="activeCategory = 'all'" :class="activeCategory === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl transition flex items-center space-x-2 cursor-pointer shrink-0">
                <i class="fa-solid fa-border-all"></i>
                <span>All Drive Files ({{ $totalFilesCount }})</span>
            </button>

            <button @click="activeCategory = 'Legal & RERA Documents'" :class="activeCategory === 'Legal & RERA Documents' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl transition flex items-center space-x-2 cursor-pointer shrink-0">
                <i class="fa-solid fa-gavel text-amber-500"></i>
                <span>Legal & RERA Approvals</span>
            </button>

            <button @click="activeCategory = 'Company Registration & Tax'" :class="activeCategory === 'Company Registration & Tax' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl transition flex items-center space-x-2 cursor-pointer shrink-0">
                <i class="fa-solid fa-building-flag text-sky-500"></i>
                <span>Company Registration & GST</span>
            </button>

            <button @click="activeCategory = 'Financial & Banking Assets'" :class="activeCategory === 'Financial & Banking Assets' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl transition flex items-center space-x-2 cursor-pointer shrink-0">
                <i class="fa-solid fa-vault text-emerald-500"></i>
                <span>Financial & Bank Audits</span>
            </button>

            <button @click="activeCategory = 'Project & Marketing Collaterals'" :class="activeCategory === 'Project & Marketing Collaterals' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl transition flex items-center space-x-2 cursor-pointer shrink-0">
                <i class="fa-solid fa-photo-film text-purple-500"></i>
                <span>Project Marketing Collaterals</span>
            </button>

            <button @click="activeCategory = 'HR & Internal Policies'" :class="activeCategory === 'HR & Internal Policies' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl transition flex items-center space-x-2 cursor-pointer shrink-0">
                <i class="fa-solid fa-book-user text-rose-500"></i>
                <span>HR Policies & Guidelines</span>
            </button>

            <button @click="activeCategory = 'General Drive Vault'" :class="activeCategory === 'General Drive Vault' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl transition flex items-center space-x-2 cursor-pointer shrink-0">
                <i class="fa-solid fa-box-archive text-slate-500"></i>
                <span>General Vault</span>
            </button>
        </div>
    </div>

    <!-- Search & Confidentiality Filter Bar -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs">
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400"></i>
            <input type="text" x-model="searchKeyword" placeholder="Search company file by title, code or notes..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:border-indigo-500 font-medium">
        </div>

        <div class="flex items-center space-x-3">
            <span class="text-slate-500 font-semibold">Access Level Filter:</span>
            <select x-model="activeConfidentiality" class="bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500">
                <option value="all">All Access Levels</option>
                <option value="Confidential (Admins Only)">ðŸ”’ Confidential (Admins Only)</option>
                <option value="Internal Team Access">ðŸ‘¥ Internal Team Access</option>
                <option value="Public / Shareable">ðŸŒ Public / Shareable</option>
            </select>
        </div>
    </div>

    <!-- Company Files Table & Ledger -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 flex justify-between items-center">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Company Private Files & Vault Ledger</h3>
                <p class="text-xs text-slate-500">Strictly isolated internal company assets</p>
            </div>
            <span class="text-xs text-slate-400 font-medium">Total: {{ $documents->count() }} records</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/90 text-slate-500 font-bold border-b border-slate-200/80 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="p-4">Document Title & Name</th>
                        <th class="p-4">Drive Folder / Category</th>
                        <th class="p-4">Confidentiality Tag</th>
                        <th class="p-4">Uploaded / Renewal Date</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/80 font-medium">
                    @forelse($documents as $doc)
                    @php
                        $isConfidential = str_contains($doc->document_number ?? '', 'Confidential');
                        $isPublic = str_contains($doc->document_number ?? '', 'Public');
                        $confCategory = $doc->document_number ?? 'Internal Team Access';

                        $isPdf = str_contains($doc->file_path, '.pdf');
                        $isImg = str_contains($doc->file_path, '.jpg') || str_contains($doc->file_path, '.png') || str_contains($doc->file_path, '.jpeg');
                    @endphp
                    <tr x-show="(activeCategory === 'all' || activeCategory === '{{ $doc->document_type }}') && (activeConfidentiality === 'all' || activeConfidentiality === '{{ $confCategory }}') && (searchKeyword === '' || '{{ strtolower($doc->notes ?? '') }}'.includes(searchKeyword.toLowerCase()) || '{{ strtolower($doc->document_type) }}'.includes(searchKeyword.toLowerCase()))" class="hover:bg-slate-50/80 transition">
                        <!-- Document Title -->
                        <td class="p-4">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-10 h-10 rounded-2xl {{ $isPdf ? 'bg-rose-50 text-rose-600 border-rose-200' : ($isImg ? 'bg-purple-50 text-purple-600 border-purple-200' : 'bg-indigo-50 text-indigo-600 border-indigo-200') }} border font-bold text-base flex items-center justify-center shrink-0">
                                    <i class="fa-solid {{ $isPdf ? 'fa-file-pdf' : ($isImg ? 'fa-file-image' : 'fa-file-lines') }}"></i>
                                </div>
                                <div>
                                    <div class="font-extrabold text-slate-900 text-sm">
                                        {{ $doc->notes ?? $doc->document_type }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                        Uploaded: {{ $doc->created_at->format('d M Y, h:i A') }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Category Folder -->
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-slate-100 text-slate-800 border border-slate-200 inline-flex items-center space-x-1.5">
                                <i class="fa-solid fa-folder text-indigo-500"></i>
                                <span>{{ $doc->document_type }}</span>
                            </span>
                        </td>

                        <!-- Confidentiality Tag -->
                        <td class="p-4">
                            @if($isConfidential)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200 inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-lock text-rose-600"></i>
                                    <span>Confidential (Admins)</span>
                                </span>
                            @elseif($isPublic)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-globe text-emerald-600"></i>
                                    <span>Public / Shareable</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-sky-50 text-sky-700 border border-sky-200 inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-users text-sky-600"></i>
                                    <span>Internal Team Access</span>
                                </span>
                            @endif
                        </td>

                        <!-- Expiry / Renewal Date -->
                        <td class="p-4">
                            @if($doc->expiry_date)
                                @php
                                    $isExpired = \Carbon\Carbon::parse($doc->expiry_date)->isPast();
                                @endphp
                                <span class="{{ $isExpired ? 'text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200' : 'text-slate-900 font-semibold' }}">
                                    <i class="fa-solid fa-calendar-days mr-1 text-slate-400"></i>
                                    {{ \Carbon\Carbon::parse($doc->expiry_date)->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-slate-400 font-medium italic">No Renewal Needed</span>
                            @endif
                        </td>

                        <!-- Action Buttons -->
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ $doc->file_path }}" target="_blank" download class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl border border-indigo-200 transition flex items-center space-x-1.5">
                                    <i class="fa-solid fa-download"></i>
                                    <span>Download</span>
                                </a>

                                @if(Auth::user()->isCompanyAdmin() || Auth::user()->isManager() || Auth::user()->isSaaSFounder())
                                <form method="POST" action="{{ route('documents.destroy', $doc->id) }}" onsubmit="return confirm('Delete this company drive file?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition cursor-pointer" title="Delete File">
                                        <i class="fa-solid fa-trash-can text-rose-500"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-400 font-medium">
                            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fa-solid fa-folder-open"></i>
                            </div>
                            No files uploaded to your Company Drive yet. Click "+ Upload Company File" to store internal documents.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upload Company File Modal -->
    <div id="uploadCompanyDriveModal" class="hidden fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white max-w-lg w-full rounded-3xl p-6 border border-slate-200 shadow-2xl space-y-5">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                    <h3 class="text-base font-extrabold text-slate-900">Upload to Company Private Drive</h3>
                </div>
                <button onclick="document.getElementById('uploadCompanyDriveModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold cursor-pointer text-lg">âœ•</button>
            </div>

            <form action="{{ route('documents.kyc.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="form-label font-bold text-slate-700">Document Title / File Name *</label>
                    <input type="text" name="file_name" required placeholder="e.g. Master Land Deed 2026 / RERA Approval License" class="form-input">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label font-bold text-slate-700">Folder / Category *</label>
                        <select name="category" required class="form-input">
                            <option value="Legal & RERA Documents">ðŸ“œ Legal & RERA Documents</option>
                            <option value="Company Registration & Tax">ðŸ›ï¸ Company Registration & GST</option>
                            <option value="Financial & Banking Assets">ðŸ’° Financial & Banking Assets</option>
                            <option value="Project & Marketing Collaterals">ðŸŽ¨ Project & Marketing Collaterals</option>
                            <option value="HR & Internal Policies">ðŸ“‹ HR & Internal Policies</option>
                            <option value="General Drive Vault">ðŸ“ General Drive Vault</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label font-bold text-slate-700">Confidentiality Level *</label>
                        <select name="confidentiality" required class="form-input">
                            <option value="Confidential (Admins Only)">ðŸ”’ Confidential (Admins Only)</option>
                            <option value="Internal Team Access" selected>ðŸ‘¥ Internal Team Access</option>
                            <option value="Public / Shareable">ðŸŒ Public / Shareable</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label font-bold text-slate-700">Select Document File *</label>
                        <input type="file" name="document_file" required class="form-input">
                        <span class="text-[10px] text-slate-400">PDF, JPG, PNG, DOCX, ZIP (Max 20MB)</span>
                    </div>

                    <div>
                        <label class="form-label font-bold text-slate-700">Renewal / Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" class="form-input font-mono">
                    </div>
                </div>

                <div>
                    <label class="form-label font-bold text-slate-700">Notes / Description (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Sanctioned layout plan approved by authority..." class="form-input"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('uploadCompanyDriveModal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs cursor-pointer">Upload File â†’</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
