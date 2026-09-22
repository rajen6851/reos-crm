@extends('layouts.reos')

@section('title', 'Broker Portal')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Top Greeting Header & Date/Time Formatting -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0F172A] tracking-tight">
                Good {{ date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening') }}, {{ $broker->agency_name ?? explode(' ', auth()->user()->name)[0] }}
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                {{ date('d M Y') }} | Indian (timezone formatting) &bull; Channel Partner Portal &bull; <strong class="text-slate-900 font-semibold">{{ $brokerLeads->count() }}</strong> referral leads submitted
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Segmented Period Selector -->
            <div class="inline-flex items-center bg-[#E2E8F0]/70 p-1 rounded-lg text-xs font-semibold text-slate-600">
                <button class="px-3 py-1.5 rounded-md bg-white text-slate-900 shadow-2xs font-bold transition">Today</button>
                <button class="px-3 py-1.5 rounded-md hover:text-slate-900 transition">This Week</button>
                <button class="px-3 py-1.5 rounded-md hover:text-slate-900 transition">This Month</button>
                <button class="px-3 py-1.5 rounded-md hover:text-slate-900 transition">Custom</button>
            </div>

            <!-- Referral Link Button -->
            @if($projects->isNotEmpty())
            @php
                $firstProj = $projects->first();
                $brokerRefUrl = route('projects.public', $firstProj->id) . '?ref=' . ($broker->id ?? 1);
            @endphp
            <button onclick="copyBrokerShareUrl('{{ $brokerRefUrl }}')" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs rounded-lg border border-slate-200 shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-link text-xs text-slate-500"></i>
                <span id="heroCopyBtnText">Copy Referral Link</span>
            </button>
            @endif

            <!-- Submit Lead Button -->
            <button onclick="document.getElementById('submitLeadModal').classList.remove('hidden')" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Submit Referral Lead</span>
            </button>
        </div>
    </div>

    <!-- 4 Key Broker Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Card 1: Total Commissions -->
        <a href="{{ $broker ? route('brokers.show', $broker->id) : route('brokers.index') }}" class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-300 transition space-y-1 block">
            <div class="flex items-center justify-between text-xs text-slate-600 font-semibold uppercase tracking-wider">
                <span>Total Earned</span>
                <i class="fa-solid fa-money-bill-wave text-emerald-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">â‚¹{{ number_format($totalCommissions) }}</div>
            <div class="text-xs font-semibold text-emerald-600">Across Bookings &rarr;</div>
        </a>

        <!-- Card 2: Approved Payouts -->
        <a href="{{ $broker ? route('brokers.show', $broker->id) : route('brokers.index') }}" class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-300 transition space-y-1 block">
            <div class="flex items-center justify-between text-xs text-slate-600 font-semibold uppercase tracking-wider">
                <span>Approved Payouts</span>
                <i class="fa-solid fa-circle-check text-blue-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-blue-700 font-mono">â‚¹{{ number_format($approvedCommissions) }}</div>
            <div class="text-xs font-semibold text-blue-600">Ready for Transfer &rarr;</div>
        </a>

        <!-- Card 3: Submitted Leads -->
        <a href="{{ route('leads.index') }}" class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-300 transition space-y-1 block">
            <div class="flex items-center justify-between text-xs text-slate-600 font-semibold uppercase tracking-wider">
                <span>Submitted Leads</span>
                <i class="fa-solid fa-chart-line text-indigo-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-indigo-700 font-mono">{{ $brokerLeads->count() }} Leads</div>
            <div class="text-xs font-semibold text-indigo-600">Active Referrals &rarr;</div>
        </a>

        <!-- Card 4: Partner Rate -->
        <div class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-semibold uppercase tracking-wider">
                <span>Partner Rate</span>
                <i class="fa-solid fa-tag text-purple-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-purple-700 font-mono">{{ number_format($broker->commission_rate ?? 2.5, 2) }}%</div>
            <div class="text-xs font-semibold text-purple-600">Commission Slab</div>
        </div>
    </div>

    <!-- My Submitted Referral Leads Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col justify-between">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A]">My Submitted Referral Leads</h2>
                <p class="text-xs text-slate-500 font-medium">Live milestone progress & status tracking</p>
            </div>
            <button onclick="document.getElementById('submitLeadModal').classList.remove('hidden')" class="px-3 py-1.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-lg transition cursor-pointer">
                + Submit New Lead
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                    <tr>
                        <th class="p-3.5">Lead Name / Code</th>
                        <th class="p-3.5">Contact Phone</th>
                        <th class="p-3.5">Interested Property</th>
                        <th class="p-3.5">Submitted Date</th>
                        <th class="p-3.5 text-right">Live Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($brokerLeads as $bl)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="p-3.5 font-bold text-slate-900">
                            <div>{{ $bl->lead->name ?? 'Buyer Customer' }}</div>
                            <div class="text-[10px] text-blue-600 font-mono font-normal">{{ $bl->lead->lead_code ?? 'LD-BRK' }}</div>
                        </td>
                        <td class="p-3.5 font-mono font-bold text-slate-900">{{ $bl->lead->phone ?? 'N/A' }}</td>
                        <td class="p-3.5 font-semibold text-slate-700">{{ $bl->project->name ?? 'Property Project' }}</td>
                        <td class="p-3.5 font-mono text-slate-500">{{ date('d M Y', strtotime($bl->submitted_at)) }}</td>
                        <td class="p-3.5 text-right">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200 uppercase">
                                {{ $bl->broker_visible_status ?? 'Submitted' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-xs text-slate-400 font-medium">
                            No referral leads submitted yet. Click "+ Submit Referral Lead" to start earning commissions!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- My Earnings & Commissions -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A] flex items-center space-x-2">
                    <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                    <span>My Earnings & Commissions</span>
                </h2>
                <p class="text-xs text-slate-500 font-medium">Track your earned commissions from successful referral bookings</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                    <tr>
                        <th class="p-3.5">Booking / Project</th>
                        <th class="p-3.5">Customer Name</th>
                        <th class="p-3.5">Commission Amount</th>
                        <th class="p-3.5">Generated Date</th>
                        <th class="p-3.5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($commissions as $c)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="p-3.5 font-bold text-slate-900">
                            <div>{{ $c->booking->booking_code ?? 'Booking' }}</div>
                            <div class="text-[10px] text-blue-600 font-mono font-normal">{{ $c->booking->project->name ?? 'Project' }}</div>
                        </td>
                        <td class="p-3.5 font-mono font-bold text-slate-900">{{ $c->booking->customer_name ?? 'N/A' }}</td>
                        <td class="p-3.5 font-semibold text-emerald-700">â‚¹{{ number_format($c->total_commission_amount) }}</td>
                        <td class="p-3.5 font-mono text-slate-500">{{ date('d M Y', strtotime($c->created_at)) }}</td>
                        <td class="p-3.5 text-right">
                            @if($c->status === 'approved' || $c->status === 'paid')
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                                    {{ $c->status }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-200 uppercase">
                                    {{ $c->status }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-xs text-slate-400 font-medium">
                            No commissions generated yet. When your referred leads book a property, your earnings will appear here!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Public Properties Catalog Grid -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A] flex items-center space-x-2">
                    <i class="fa-solid fa-globe text-blue-600"></i>
                    <span>Public Properties Catalog & Shareable Links</span>
                </h2>
                <p class="text-xs text-slate-500 font-medium">Live builder projects available for channel partner customer referrals</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($projects as $proj)
            @php
                $shareUrl = route('projects.public', $proj->id) . '?ref=' . ($broker->id ?? 1);
            @endphp
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-slate-300 transition space-y-3 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200 uppercase">
                            {{ $proj->city }}
                        </span>
                        <span class="text-xs font-mono font-bold text-emerald-600">
                            {{ $proj->availableUnitsCount() }} Units Free
                        </span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">{{ $proj->name }}</h3>
                    <p class="text-xs text-slate-500 line-clamp-2">{{ $proj->location }}</p>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-between gap-2">
                    <a href="{{ route('projects.public', $proj->id) }}" target="_blank" class="text-xs font-bold text-slate-700 hover:text-blue-600">
                        Preview Showcase &rarr;
                    </a>
                    <button onclick="copyBrokerShareUrl('{{ $shareUrl }}')" class="px-3 py-1.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-lg transition shadow-2xs">
                        Copy Link
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal: Submit Customer Referral Lead -->
<div id="submitLeadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
    <div class="bg-white max-w-md w-full rounded-xl p-6 border border-slate-200 shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-slate-200">
            <h3 class="text-sm font-bold text-[#0F172A]">Submit Customer Referral Lead</h3>
            <button onclick="document.getElementById('submitLeadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">âœ•</button>
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

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('submitLeadModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-lg border border-slate-200">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold rounded-lg shadow-xs">Submit Lead Now</button>
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

