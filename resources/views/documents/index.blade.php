@extends('layouts.reos')

@section('title', 'KYC & Document Vault – REOS Enterprise')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ activeTab: 'all' }">
    <!-- Header Banner -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-[#E2E8F0] shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-[#4F46E5] uppercase tracking-wider border border-indigo-100">
                <i class="fa-solid fa-folder-open text-[#4F46E5]"></i>
                <span>Enterprise Document Management Vault</span>
            </div>
            <h1 class="page-heading text-2xl font-extrabold text-[#0F172A] tracking-tight">KYC & Digital File Repository</h1>
            <p class="body-text text-xs text-[#64748B]">
                Organized identity verification, RERA licenses, partnership deeds, customer PAN/Aadhar cards, and employee records.
            </p>
        </div>

        <button onclick="document.getElementById('uploadKycModal').classList.remove('hidden')" class="px-5 py-3 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
            <i class="fa-solid fa-cloud-arrow-up text-white text-sm"></i>
            <span>+ Upload KYC Document</span>
        </button>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Total Repository Files</span>
                <div class="text-2xl font-extrabold text-[#0F172A] mt-1 font-mono">{{ $documents->count() }} Files</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-[#4F46E5] flex items-center justify-center text-lg border border-indigo-100"><i class="fa-solid fa-folder-tree"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Expiring Soon (30 Days)</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-1 font-mono">{{ $expiringSoonCount }} Files</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg border border-amber-200"><i class="fa-solid fa-clock-rotate-left"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Expired Documents</span>
                <div class="text-2xl font-extrabold text-[#DC2626] mt-1 font-mono">{{ $expiredCount }} Files</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-rose-50 text-[#DC2626] flex items-center justify-center text-lg border border-rose-200"><i class="fa-solid fa-circle-exclamation"></i></span>
        </div>
    </div>

    <!-- Category Filter Tabs Bar (Organized View) -->
    <div class="flex items-center space-x-2 bg-white p-2 rounded-2xl border border-[#E2E8F0] shadow-2xs overflow-x-auto text-xs font-semibold">
        <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-[#FEF2F2] text-[#DC2626] border-[#FEE2E2]' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>All Documents ({{ $documents->count() }})</span>
        </button>

        <button @click="activeTab = 'customer'" :class="activeTab === 'customer' ? 'bg-sky-50 text-sky-800 border-sky-200' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-user-tag text-sky-600"></i>
            <span>Customer / Purchaser KYC ({{ $documents->filter(fn($d) => str_contains($d->documentable_type, 'Lead'))->count() }})</span>
        </button>

        <button @click="activeTab = 'broker'" :class="activeTab === 'broker' ? 'bg-amber-50 text-amber-900 border-amber-200' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-handshake text-amber-600"></i>
            <span>Broker & Partner Licenses ({{ $documents->filter(fn($d) => str_contains($d->documentable_type, 'Broker'))->count() }})</span>
        </button>

        <button @click="activeTab = 'employee'" :class="activeTab === 'employee' ? 'bg-purple-50 text-purple-900 border-purple-200' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-user-tie text-purple-600"></i>
            <span>Staff Employee Records ({{ $documents->filter(fn($d) => str_contains($d->documentable_type, 'User'))->count() }})</span>
        </button>
    </div>

    <!-- Organized Documents List Table -->
    <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] flex justify-between items-center">
            <h3 class="section-heading text-base">Organized Digital Documents Ledger</h3>
            <span class="text-xs text-[#64748B] font-medium">Sorted by upload date</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[#64748B] font-bold border-b border-[#E2E8F0] uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="p-4">Entity Type & Owner Name</th>
                        <th class="p-4">Document Details</th>
                        <th class="p-4">Doc Number</th>
                        <th class="p-4">Expiry Date</th>
                        <th class="p-4">Verification Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] font-medium">
                    @forelse($documents as $doc)
                    @php
                        $isCustomer = str_contains($doc->documentable_type, 'Lead');
                        $isBroker = str_contains($doc->documentable_type, 'Broker');
                        $isUser = str_contains($doc->documentable_type, 'User');

                        $tabCategory = $isCustomer ? 'customer' : ($isBroker ? 'broker' : 'employee');

                        $entityName = 'N/A';
                        $entitySub = 'Record ID #' . $doc->documentable_id;
                        $entityPhone = '';

                        if ($isCustomer && $doc->documentable) {
                            $entityName = $doc->documentable->first_name . ' ' . $doc->documentable->last_name;
                            $entitySub = 'Customer Code: ' . ($doc->documentable->lead_code ?? 'LD-' . $doc->documentable_id);
                            $entityPhone = $doc->documentable->phone;
                        } elseif ($isBroker && $doc->documentable) {
                            $entityName = $doc->documentable->agency_name;
                            $entitySub = 'Broker Code: ' . ($doc->documentable->broker_code ?? 'BRK-' . $doc->documentable_id);
                            $entityPhone = $doc->documentable->phone;
                        } elseif ($isUser && $doc->documentable) {
                            $entityName = $doc->documentable->name;
                            $entitySub = 'Staff Designation: ' . ($doc->documentable->designation ?? 'Employee');
                            $entityPhone = $doc->documentable->phone ?? $doc->documentable->email;
                        }
                    @endphp
                    <tr x-show="activeTab === 'all' || activeTab === '{{ $tabCategory }}'" class="hover:bg-slate-50/80 transition">
                        <!-- Entity Owner Column -->
                        <td class="p-4">
                            <div class="flex items-center space-x-3">
                                @if($isCustomer)
                                    <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-700 font-bold text-xs flex items-center justify-center border border-sky-200 shrink-0">
                                        <i class="fa-solid fa-user text-sky-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-[#0F172A] text-sm">{{ $entityName }}</div>
                                        <div class="text-[11px] text-[#64748B] font-mono">{{ $entitySub }}</div>
                                        @if($entityPhone)<div class="text-[10px] text-sky-700 font-mono font-bold"><i class="fa-solid fa-phone mr-1"></i>{{ $entityPhone }}</div>@endif
                                    </div>
                                @elseif($isBroker)
                                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 font-bold text-xs flex items-center justify-center border border-amber-200 shrink-0">
                                        <i class="fa-solid fa-handshake text-amber-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-[#0F172A] text-sm">{{ $entityName }}</div>
                                        <div class="text-[11px] text-[#64748B] font-mono">{{ $entitySub }}</div>
                                        @if($entityPhone)<div class="text-[10px] text-amber-800 font-mono font-bold"><i class="fa-solid fa-phone mr-1"></i>{{ $entityPhone }}</div>@endif
                                    </div>
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 font-bold text-xs flex items-center justify-center border border-purple-200 shrink-0">
                                        <i class="fa-solid fa-user-tie text-purple-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-[#0F172A] text-sm">{{ $entityName }}</div>
                                        <div class="text-[11px] text-[#64748B] font-mono">{{ $entitySub }}</div>
                                        @if($entityPhone)<div class="text-[10px] text-purple-700 font-mono font-bold">{{ $entityPhone }}</div>@endif
                                    </div>
                                @endif
                            </div>
                        </td>

                        <!-- Document Details Column -->
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-50 text-[#4F46E5] border border-indigo-200 inline-flex items-center space-x-1.5">
                                <i class="fa-solid fa-file-pdf text-[#4F46E5]"></i>
                                <span>{{ $doc->document_type }}</span>
                            </span>
                            @if($doc->notes)
                                <div class="text-[11px] text-[#64748B] mt-1 font-medium italic">"{{ $doc->notes }}"</div>
                            @endif
                        </td>

                        <!-- Document Number -->
                        <td class="p-4 font-mono font-bold text-[#0F172A]">
                            {{ $doc->document_number ?? 'N/A' }}
                        </td>

                        <!-- Expiry Date -->
                        <td class="p-4">
                            @if($doc->expiry_date)
                                @php
                                    $isExpired = \Carbon\Carbon::parse($doc->expiry_date)->isPast();
                                @endphp
                                <span class="{{ $isExpired ? 'text-[#DC2626] font-bold bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200' : 'text-[#0F172A] font-semibold' }}">
                                    {{ \Carbon\Carbon::parse($doc->expiry_date)->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-slate-400 font-medium">No Expiry Date</span>
                            @endif
                        </td>

                        <!-- Verification Status -->
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-[#059669] border border-emerald-200 inline-flex items-center space-x-1">
                                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                <span>Verified</span>
                            </span>
                        </td>

                        <!-- Action Buttons -->
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ $doc->file_path }}" target="_blank" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-[#4F46E5] btn-text rounded-xl border border-indigo-200 transition flex items-center space-x-1">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    <span>View File</span>
                                </a>

                                <form method="POST" action="{{ route('documents.destroy', $doc->id) }}" onsubmit="return confirm('Delete this KYC document file?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-[#DC2626] transition" title="Delete File">
                                        <i class="fa-solid fa-trash-can text-rose-500"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 font-medium">
                            No organized KYC documents uploaded yet. Click "+ Upload KYC Document" to attach files.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upload KYC Modal (With Entity Dropdowns) -->
    <div id="uploadKycModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white max-w-lg w-full rounded-3xl p-6 border border-[#E2E8F0] shadow-2xl space-y-5">
            <div class="flex justify-between items-center pb-3 border-b border-[#E2E8F0]">
                <h3 class="section-heading text-lg">Upload & Organize KYC File</h3>
                <button onclick="document.getElementById('uploadKycModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <form action="{{ route('documents.kyc.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="form-label">Select Entity Category *</label>
                    <select id="entityCategorySelect" name="documentable_type" onchange="toggleEntityOptions()" required class="form-input">
                        <option value="App\Models\Lead">Customer / Purchaser (Lead)</option>
                        <option value="App\Models\Broker">Broker</option>
                        <option value="App\Models\User">Company Staff / Employee</option>
                    </select>
                </div>

                <!-- Entity Selection Dropdowns -->
                <div id="leadSelectWrapper">
                    <label class="form-label">Select Customer Lead *</label>
                    <select id="leadSelect" name="documentable_id" class="form-input">
                        @foreach($leads as $l)
                            <option value="{{ $l->id }}">{{ $l->first_name }} {{ $l->last_name }} ({{ $l->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div id="brokerSelectWrapper" class="hidden">
                    <label class="form-label">Select Broker *</label>
                    <select id="brokerSelect" disabled name="documentable_id" class="form-input">
                        @foreach($brokers as $b)
                            <option value="{{ $b->id }}">{{ $b->agency_name }} ({{ $b->broker_code }} - {{ $b->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div id="userSelectWrapper" class="hidden">
                    <label class="form-label">Select Staff Employee *</label>
                    <select id="userSelect" disabled name="documentable_id" class="form-input">
                        @foreach($teamUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Document Type *</label>
                        <select name="document_type" required class="form-input">
                            <option value="Aadhar Card">Aadhar Card</option>
                            <option value="PAN Card">PAN Card</option>
                            <option value="RERA License">RERA License</option>
                            <option value="Partnership Deed">Partnership Deed</option>
                            <option value="GST Certificate">GST Certificate</option>
                            <option value="Passport">Passport</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Doc Number / ID</label>
                        <input type="text" name="document_number" placeholder="ABCDE1234F" class="form-input font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Upload File (PDF/Image) *</label>
                        <input type="file" name="document_file" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" class="form-input font-mono">
                    </div>
                </div>

                <div>
                    <label class="form-label">Notes / Instructions</label>
                    <textarea name="notes" rows="2" placeholder="Self-attested KYC document..." class="form-input"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-[#E2E8F0]">
                    <button type="button" onclick="document.getElementById('uploadKycModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-[#0F172A] btn-text rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text rounded-xl shadow-xs">Upload & Organize File →</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleEntityOptions() {
        const cat = document.getElementById('entityCategorySelect').value;
        const lWrap = document.getElementById('leadSelectWrapper');
        const bWrap = document.getElementById('brokerSelectWrapper');
        const uWrap = document.getElementById('userSelectWrapper');

        const lSel = document.getElementById('leadSelect');
        const bSel = document.getElementById('brokerSelect');
        const uSel = document.getElementById('userSelect');

        lWrap.classList.add('hidden');
        bWrap.classList.add('hidden');
        uWrap.classList.add('hidden');

        lSel.disabled = true;
        bSel.disabled = true;
        uSel.disabled = true;

        if (cat.includes('Lead')) {
            lWrap.classList.remove('hidden');
            lSel.disabled = false;
        } else if (cat.includes('Broker')) {
            bWrap.classList.remove('hidden');
            bSel.disabled = false;
        } else if (cat.includes('User')) {
            uWrap.classList.remove('hidden');
            uSel.disabled = false;
        }
    }
</script>
@endsection
