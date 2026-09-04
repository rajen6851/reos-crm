@extends('layouts.reos')

@section('title', 'Channel Partner & Broker Portal – REOS')

@section('content')
<div class="space-y-6 pb-12 max-w-7xl mx-auto px-2 sm:px-4">
    <!-- Hero Banner: ERP Greeting & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-2xs">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 uppercase tracking-wider border border-amber-200 mb-2">
                <span class="w-2 h-2 rounded-full bg-amber-600 animate-pulse"></span>
                <span>Channel Partner Portal</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center space-x-2">
                <span>{{ $broker->agency_name ?? $user->name }}</span>
                <i class="fa-solid fa-handshake text-[#059669] text-2xl"></i>
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">
                Submit customer leads, monitor real-time milestone status, share public property links with buyers, and track earned commission payouts.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            @if($projects->isNotEmpty())
            @php
                $firstProj = $projects->first();
                $brokerRefUrl = route('projects.public', $firstProj->id) . '?ref=' . ($broker->id ?? 1);
            @endphp
            <button onclick="copyBrokerShareUrl('{{ $brokerRefUrl }}')" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <span id="heroCopyBtnText"><i class="fa-solid fa-link mr-1"></i>Copy Referral Link</span>
            </button>
            @endif

            <button onclick="document.getElementById('submitLeadModal').classList.remove('hidden')" class="px-5 py-2.5 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-[#DC2626] fa-plus text-xs"></i>
                <span>+ Submit Customer Lead</span>
            </button>
        </div>
    </div>

    <!-- Stat Cards Grid with Micro-Trends (Clickable Widgets) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        <!-- Card 1: Total Commissions -->
        <a href="{{ $broker ? route('brokers.show', $broker->id) : route('brokers.index') }}" class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer space-y-2 block">
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Earned</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center font-bold text-base"><i class="fa-solid fa-money-bill-wave"></i></div>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">₹{{ number_format($totalCommissions) }}</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                <i class="fa-solid fa-arrow-up text-[9px]"></i>
                <span>Across Bookings →</span>
            </div>
        </a>

        <!-- Card 2: Approved Payouts -->
        <a href="{{ $broker ? route('brokers.show', $broker->id) : route('brokers.index') }}" class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer space-y-2 block">
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Approved Payouts</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-base"><i class="fa-solid fa-circle-check"></i></div>
            </div>
            <div class="text-2xl font-bold text-emerald-700 font-mono">₹{{ number_format($approvedCommissions) }}</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                <i class="fa-solid fa-check text-[9px]"></i>
                <span>Ready for Transfer →</span>
            </div>
        </a>

        <!-- Card 3: Submitted Leads -->
        <a href="{{ route('leads.index') }}" class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer space-y-2 block">
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Submitted Leads</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-base"><i class="fa-solid fa-chart-line"></i></div>
            </div>
            <div class="text-2xl font-bold text-indigo-700 font-mono">{{ $brokerLeads->count() }} Leads</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">
                <i class="fa-solid fa-user-plus text-[9px]"></i>
                <span>Active Referrals →</span>
            </div>
        </a>

        <!-- Card 4: Partner Rate -->
        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-2xs space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Partner Rate</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center font-bold text-base"><i class="fa-solid fa-tag"></i></div>
            </div>
            <div class="text-2xl font-bold text-sky-700 font-mono">{{ number_format($broker->commission_rate ?? 2.5, 2) }}%</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-full">
                <i class="fa-solid fa-handshake text-[9px]"></i>
                <span>Default Rate</span>
            </div>
        </div>
    </div>

    <!-- My Submitted Referral Leads Table -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">My Submitted Referral Leads</h2>
                <p class="text-xs text-slate-500">Live milestone progress & status tracking</p>
            </div>
            <button onclick="document.getElementById('submitLeadModal').classList.remove('hidden')" class="px-4 py-2 bg-[#DC2626] hover:bg-[#B91C1C] text-white font-bold text-xs rounded-xl transition cursor-pointer">
                + Submit New Lead
            </button>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Lead Name / Code</th>
                        <th class="py-3 px-4">Contact Phone</th>
                        <th class="py-3 px-4">Interested Property</th>
                        <th class="py-3 px-4">Submitted Date</th>
                        <th class="py-3 px-4 text-right">Live Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($brokerLeads as $bl)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            <div>{{ $bl->lead->name ?? 'Buyer Customer' }}</div>
                            <div class="text-[10px] text-indigo-600 font-mono">{{ $bl->lead->lead_code ?? 'LD-BRK' }}</div>
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $bl->lead->phone ?? 'N/A' }}</td>
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $bl->project->name ?? 'Property Project' }}</td>
                        <td class="py-3 px-4 font-mono text-slate-500">{{ date('d M Y', strtotime($bl->submitted_at)) }}</td>
                        <td class="py-3 px-4 text-right">
                            <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase">
                                {{ $bl->broker_visible_status ?? 'Submitted' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-xs text-slate-400 font-medium">No referral leads submitted yet. Click "+ Submit Customer Lead" to start earning commissions!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Public Properties Catalog Grid -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-globe text-indigo-600"></i>
                    <span>Public Properties Catalog & Shareable Links</span>
                </h2>
                <p class="text-xs text-slate-500">Live builder projects available for channel partner customer referrals</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($projects as $proj)
            @php
                $shareUrl = route('projects.public', $proj->id) . '?ref=' . ($broker->id ?? 1);
            @endphp
            <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-md transition space-y-3 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase">
                            {{ $proj->city }}
                        </span>
                        <span class="text-xs font-mono font-bold text-emerald-600">
                            {{ $proj->availableUnitsCount() }} Units Free
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">{{ $proj->name }}</h3>
                    <p class="text-xs text-slate-500 line-clamp-2">{{ $proj->location }}</p>
                </div>

                <div class="pt-3 border-t border-slate-200/60 flex items-center justify-between gap-2">
                    <a href="{{ route('projects.public', $proj->id) }}" target="_blank" class="text-xs font-bold text-slate-700 hover:text-indigo-600">
                        Preview Showcase ↗
                    </a>
                    <button onclick="copyBrokerShareUrl('{{ $shareUrl }}')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-xs">
                        Copy Link
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal: Submit Customer Referral Lead -->
<div id="submitLeadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-6 border border-[#E2E8F0] shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-[#E2E8F0]">
            <h3 class="section-heading text-base">Submit Customer Referral Lead</h3>
            <button onclick="document.getElementById('submitLeadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form action="{{ route('broker.submit-lead') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="form-label">First Name *</label>
                <input type="text" name="first_name" required placeholder="Enter buyer first name..." class="form-input">
            </div>

            <div>
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" placeholder="Enter buyer last name..." class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Phone Number *</label>
                    <input type="tel" name="phone" required placeholder="e.g. 9876543210" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" placeholder="buyer@example.com" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Interested Property Project *</label>
                <select name="project_id" required class="form-input">
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->company->name ?? 'Builder' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Notes / Customer Requirements</label>
                <textarea name="notes" rows="2" placeholder="Looking for 3BHK, budget ~85L..." class="form-input"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t border-[#E2E8F0]">
                <button type="button" onclick="document.getElementById('submitLeadModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-[#0F172A] btn-text rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text rounded-xl shadow-xs">Submit Lead Now</button>
            </div>
        </form>
    </div>
</div>

<!-- Copy Link Script -->
<script>
    function copyBrokerShareUrl(url) {
        navigator.clipboard.writeText(url);
        alert('Referral link copied to clipboard!');
    }
</script>
@endsection
