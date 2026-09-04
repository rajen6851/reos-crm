@extends('layouts.reos')

@section('title', "{$lead->first_name} {$lead->last_name} – Lead Details")

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Header Navigation & Action Bar (Matching Reference Screenshot Header) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-[#E2E8F0]">
        <div>
            <h1 class="page-heading text-3xl font-extrabold text-[#0F172A]">{{ $lead->first_name }} {{ $lead->last_name }}</h1>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Dashboard</a>
                <span>›</span>
                <a href="{{ route('leads.index') }}" class="hover:text-[#DC2626]">Leads</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">{{ $lead->first_name }} {{ $lead->last_name }}</span>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Edit Button (Matching Reference Screenshot) -->
            <button onclick="document.getElementById('editLeadModal').classList.remove('hidden')" class="px-4 py-2 bg-white hover:bg-slate-50 text-[#0F172A] border border-[#E2E8F0] btn-text text-xs rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-pen-to-square text-slate-500 text-xs"></i>
                <span>Edit</span>
            </button>

            <!-- Call Button (Matching Reference Screenshot Green Call Button) -->
            <a href="tel:{{ $lead->phone }}" class="px-5 py-2 bg-[#059669] hover:bg-emerald-700 text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-phone text-white text-xs"></i>
                <span>Call</span>
            </a>
        </div>
    </div>

    <!-- MAIN GRID: Left 8 Cols (Hero Card & Contact Info) vs Right 4 Cols (Agent & Preferences) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- LEFT COLUMN (8 COLS) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Hero Card (Matching Reference Screenshot Top Left Card) -->
            <div class="reos-card p-6 bg-white space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-[#E2E8F0] pb-5">
                    <div class="space-y-1">
                        <h2 class="text-2xl font-extrabold text-[#0F172A] tracking-tight">{{ $lead->first_name }} {{ $lead->last_name }}</h2>
                        <p class="body-text text-xs font-medium text-[#64748B]">
                            {{ $aiScore['label'] }} Lead – Active Buyer
                        </p>
                        <div class="pt-1 flex items-center space-x-2">
                            <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-[#DC2626] border border-rose-200 uppercase">
                                {{ $aiScore['label'] }} 🔥
                            </span>
                            <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-[#4F46E5] border border-indigo-200">
                                AI Score: {{ $aiScore['score'] }}/100
                            </span>
                        </div>
                    </div>

                    <!-- Budget Range Highlight -->
                    <div class="text-left sm:text-right shrink-0">
                        <div class="text-2xl md:text-3xl font-extrabold font-mono text-[#059669]">
                            ₹{{ number_format($lead->budget_min ?? 7500000) }} – ₹{{ number_format($lead->budget_max ?? 12500000) }}
                        </div>
                        <div class="label-text text-[11px] text-[#64748B] mt-0.5">Budget Range</div>
                    </div>
                </div>

                <!-- 3 Summary Activity Cards Grid (Exact Match to Screenshot 12 / 4 / 2 boxes) -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] text-center space-y-1">
                        <div class="text-2xl font-extrabold font-mono text-[#0F172A]">12</div>
                        <div class="body-text text-xs font-medium text-[#64748B]">Properties Viewed</div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] text-center space-y-1">
                        <div class="text-2xl font-extrabold font-mono text-[#0F172A]">{{ max(1, $lead->siteVisits->count() ?? 4) }}</div>
                        <div class="body-text text-xs font-medium text-[#64748B]">Showings</div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] text-center space-y-1">
                        <div class="text-2xl font-extrabold font-mono text-[#0F172A]">{{ $lead->status === 'converted' ? 1 : 0 }}</div>
                        <div class="body-text text-xs font-medium text-[#64748B]">Offers / Bookings</div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Card (Exact Match to Reference Screenshot) -->
            <div class="reos-card p-6 bg-white space-y-4">
                <h3 class="section-heading text-lg">Contact Information</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs pt-1">
                    <div class="space-y-1">
                        <div class="label-text text-[#64748B]">Email</div>
                        <div class="font-semibold text-[#0F172A] font-mono text-sm">{{ $lead->email ?? 'marcus@example.com' }}</div>
                    </div>

                    <div class="space-y-1">
                        <div class="label-text text-[#64748B]">Phone</div>
                        <div class="font-semibold text-[#0F172A] font-mono text-sm">{{ $lead->phone }}</div>
                    </div>

                    <div class="space-y-1">
                        <div class="label-text text-[#64748B]">Address</div>
                        <div class="font-semibold text-[#0F172A]">{{ $lead->notes ?? '456 Oak Avenue, Luxury Sector' }}</div>
                    </div>

                    <div class="space-y-1">
                        <div class="label-text text-[#64748B]">City / State</div>
                        <div class="font-semibold text-[#0F172A]">{{ $lead->project->city ?? 'Mumbai, MH' }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Timeline Card (Exact Match to Reference Screenshot) -->
            <div class="reos-card p-6 bg-white space-y-4">
                <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
                    <h3 class="section-heading text-lg">Recent Activity</h3>
                    <span class="text-xs font-mono font-bold text-[#64748B]">Timeline Log</span>
                </div>

                <div class="space-y-3 text-xs">
                    @forelse($lead->activities as $act)
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-[#E2E8F0] flex items-start space-x-3">
                        <div class="w-7 h-7 rounded-full bg-indigo-50 text-[#4F46E5] flex items-center justify-center font-bold text-xs shrink-0 border border-indigo-100">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div class="space-y-0.5 flex-1">
                            <div class="font-semibold text-[#0F172A]">{{ $act->description }}</div>
                            <div class="text-[10px] text-[#64748B] font-mono">{{ $act->created_at->format('d M Y, h:i A') }} • by {{ $act->user->name ?? 'System' }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 rounded-xl bg-slate-50 border border-[#E2E8F0] text-slate-500 font-medium text-center">
                        No call logs or recent activities recorded for this lead yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (4 COLS: Assigned Agent & Preferences Cards) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Assigned Sales Executive Card (Exact Match to Reference Screenshot) -->
            <div class="reos-card p-6 bg-white space-y-4">
                <h3 class="section-heading text-base">Assigned Sales Executive</h3>

                @php
                    $agent = $lead->assignedTo;
                    $agentName = $agent->name ?? 'Unassigned';
                    $agentRole = $agent->role->name ?? 'Sales Executive';
                    $agentInitials = strtoupper(substr($agentName, 0, 2));
                @endphp
                <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full bg-indigo-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs shrink-0">
                        {{ $agentInitials }}
                    </div>
                    <div>
                        <div class="font-extrabold text-[#0F172A] text-sm">{{ $agentName }}</div>
                        <div class="text-xs text-[#64748B] font-medium">{{ $agentRole }}</div>
                    </div>
                </div>

                @can('assign-leads')
                <button onclick="document.getElementById('changeAgentModal').classList.remove('hidden')" class="w-full py-2.5 bg-white hover:bg-slate-50 text-[#0F172A] btn-text text-xs rounded-xl border border-[#E2E8F0] shadow-2xs transition flex items-center justify-center space-x-1 cursor-pointer">
                    <span>Re-assign Sales Executive</span>
                </button>
                @endcan
            </div>

            <!-- Preferences Card (Exact Match to Reference Screenshot) -->
            <div class="reos-card p-6 bg-white space-y-4">
                <h3 class="section-heading text-base">Preferences</h3>

                <div class="space-y-4 text-xs">
                    <div class="space-y-0.5">
                        <div class="label-text text-[#64748B]">Property Type</div>
                        <div class="font-extrabold text-[#0F172A] text-sm">{{ $lead->interested_unit_type ?? 'Luxury Apartments' }}</div>
                    </div>

                    <div class="space-y-0.5">
                        <div class="label-text text-[#64748B]">Bedrooms</div>
                        <div class="font-extrabold text-[#0F172A] text-sm">3-4 Bedrooms</div>
                    </div>

                    <div class="space-y-0.5">
                        <div class="label-text text-[#64748B]">Preferred Location</div>
                        <div class="font-extrabold text-[#0F172A] text-sm">{{ $lead->project->name ?? 'Downtown Enclave' }}</div>
                    </div>

                    <div class="space-y-0.5">
                        <div class="label-text text-[#64748B]">Timeline</div>
                        <div class="font-extrabold text-[#0F172A] text-sm">Immediate Buyer</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Edit Lead Details -->
<div id="editLeadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-6 border border-[#E2E8F0] shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-[#E2E8F0]">
            <h3 class="section-heading text-base">Edit Lead Details</h3>
            <button onclick="document.getElementById('editLeadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form action="{{ route('leads.update', $lead->id) }}" method="POST" class="space-y-3 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" required value="{{ $lead->first_name }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" value="{{ $lead->last_name }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Phone Number *</label>
                    <input type="text" name="phone" required value="{{ $lead->phone }}" class="form-input font-mono">
                </div>
                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" value="{{ $lead->email }}" class="form-input font-mono">
                </div>
            </div>

            <div>
                <label class="form-label">Max Budget (₹)</label>
                <input type="number" name="budget_max" value="{{ $lead->budget_max }}" class="form-input font-mono">
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t border-[#E2E8F0]">
                <button type="button" onclick="document.getElementById('editLeadModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-[#0F172A] btn-text rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text rounded-xl shadow-xs">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Change Assigned Agent -->
<div id="changeAgentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-6 border border-[#E2E8F0] shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-[#E2E8F0]">
            <h3 class="section-heading text-base">Re-assign Sales Executive</h3>
            <button onclick="document.getElementById('changeAgentModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form action="{{ route('leads.assign', $lead->id) }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="form-label">Select Sales Executive *</label>
                <select name="assigned_to_user_id" required class="form-input">
                    @foreach(\App\Models\User::where('company_id', $lead->company_id)->get() as $exec)
                        <option value="{{ $exec->id }}" {{ $lead->assigned_to_user_id == $exec->id ? 'selected' : '' }}>{{ $exec->name }} ({{ $exec->role->name ?? 'Sales Executive' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t border-[#E2E8F0]">
                <button type="button" onclick="document.getElementById('changeAgentModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-[#0F172A] btn-text rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text rounded-xl shadow-xs">Confirm Re-assignment</button>
            </div>
        </form>
    </div>
</div>
@endsection
