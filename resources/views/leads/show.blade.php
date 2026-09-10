@extends('layouts.reos')

@section('title', "{$lead->first_name} {$lead->last_name} – Lead Details")

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Navigation & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600">Home</a>
                <span>›</span>
                <a href="{{ route('leads.index') }}" class="hover:text-blue-600">Leads</a>
                <span>›</span>
                <span class="text-slate-900 font-bold">{{ $lead->first_name }} {{ $lead->last_name }}</span>
            </div>
            <h1 class="text-xl font-bold text-[#0F172A] tracking-tight flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-base shadow-2xs">
                    <i class="fa-solid fa-user-tag"></i>
                </div>
                <span>{{ $lead->first_name }} {{ $lead->last_name }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    {{ $lead->lead_code }}
                </span>
            </h1>
        </div>

        <div class="flex items-center space-x-2.5">
            <button onclick="document.getElementById('editLeadModal').classList.remove('hidden')" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 font-bold text-xs rounded-lg shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-pen-to-square text-slate-500 text-xs"></i>
                <span>Edit Lead</span>
            </button>

            <a href="tel:{{ $lead->phone }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-phone text-xs"></i>
                <span>Call Customer</span>
            </a>
        </div>
    </div>

    <!-- MAIN GRID: Left 8 Cols vs Right 4 Cols -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- LEFT COLUMN (8 COLS) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Hero Card -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-200 pb-5">
                    <div class="space-y-1.5">
                        <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">{{ $lead->first_name }} {{ $lead->last_name }}</h2>
                        <p class="text-xs font-medium text-slate-500">
                            {{ $aiScore['label'] }} Priority Lead – Active Property Buyer
                        </p>
                        <div class="pt-1 flex items-center space-x-2">
                            <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase flex items-center space-x-1">
                                <span>{{ $aiScore['label'] }}</span>
                                <i class="fa-solid fa-fire text-rose-500"></i>
                            </span>
                            <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                AI Score: {{ $aiScore['score'] }}/100
                            </span>
                        </div>
                    </div>

                    <!-- Budget Range Highlight -->
                    <div class="text-left sm:text-right shrink-0">
                        <div class="text-2xl md:text-3xl font-bold font-mono text-emerald-600">
                            ₹{{ number_format($lead->budget_min ?? 7500000) }} – ₹{{ number_format($lead->budget_max ?? 12500000) }}
                        </div>
                        <div class="text-[11px] font-semibold text-slate-400 mt-0.5 uppercase tracking-wider">Customer Budget Range</div>
                    </div>
                </div>

                <!-- 3 Summary Activity Cards Grid -->
                <div class="grid grid-cols-3 gap-4 text-xs">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center space-y-1">
                        <div class="text-2xl font-bold font-mono text-[#0F172A]">12</div>
                        <div class="text-xs font-medium text-slate-500">Properties Viewed</div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center space-y-1">
                        <div class="text-2xl font-bold font-mono text-[#0F172A]">{{ max(1, $lead->siteVisits->count() ?? 4) }}</div>
                        <div class="text-xs font-medium text-slate-500">Site Showings</div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center space-y-1">
                        <div class="text-2xl font-bold font-mono text-[#0F172A]">{{ $lead->status === 'converted' ? 1 : 0 }}</div>
                        <div class="text-xs font-medium text-slate-500">Converted Bookings</div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <h3 class="text-base font-bold text-[#0F172A] flex items-center space-x-2 border-b border-slate-200 pb-3">
                    <i class="fa-solid fa-address-book text-blue-600"></i>
                    <span>Contact & Location Details</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs pt-1">
                    <div class="space-y-1">
                        <div class="text-slate-400 font-semibold text-[11px] uppercase tracking-wider">Email Address</div>
                        <div class="font-bold text-slate-900 font-mono text-xs">{{ $lead->email ?? 'N/A' }}</div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-slate-400 font-semibold text-[11px] uppercase tracking-wider">Phone Number</div>
                        <div class="font-bold text-slate-900 font-mono text-xs flex items-center space-x-2">
                            <span>{{ $lead->phone }}</span>
                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">WA</a>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-slate-400 font-semibold text-[11px] uppercase tracking-wider">Address / Notes</div>
                        <div class="font-semibold text-slate-800">{{ $lead->notes ?? 'N/A' }}</div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-slate-400 font-semibold text-[11px] uppercase tracking-wider">Location / Project</div>
                        <div class="font-semibold text-slate-800">{{ $lead->project->name ?? 'N/A' }} ({{ $lead->project->city ?? 'N/A' }})</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Timeline Card -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <h3 class="text-base font-bold text-[#0F172A] flex items-center space-x-2">
                        <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                        <span>Recent Activity & Interaction Logs</span>
                    </h3>
                </div>

                <div class="space-y-3 text-xs">
                    @forelse($lead->activities as $act)
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0 border border-blue-200">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div class="space-y-0.5 flex-1">
                            <div class="font-bold text-slate-900">{{ $act->description }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $act->created_at->format('d M Y, h:i A') }} • by {{ $act->user->name ?? 'System' }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 font-medium text-center">
                        No call logs or recent activities recorded for this lead yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (4 COLS) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Assigned Sales Executive Card -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <h3 class="text-sm font-bold text-[#0F172A] border-b border-slate-200 pb-3 flex items-center space-x-2">
                    <i class="fa-solid fa-user-tie text-blue-600"></i>
                    <span>Assigned Sales Executive</span>
                </h3>

                @php
                    $agent = $lead->assignedTo;
                    $agentName = $agent->name ?? 'Unassigned';
                    $agentRole = $agent->role->name ?? 'Sales Executive';
                    $agentInitials = strtoupper(substr($agentName, 0, 2));
                @endphp
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-[#0F172A] text-white font-bold text-xs flex items-center justify-center shadow-2xs shrink-0">
                        {{ $agentInitials }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 text-xs">{{ $agentName }}</div>
                        <div class="text-[10px] text-slate-500 font-medium">{{ $agentRole }}</div>
                    </div>
                </div>

                @can('assign-leads')
                <button onclick="document.getElementById('changeAgentModal').classList.remove('hidden')" class="w-full py-2 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs rounded-lg border border-slate-200 shadow-2xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-user-pen text-slate-500 text-xs"></i>
                    <span>Re-assign Executive</span>
                </button>
                @endcan
            </div>

            <!-- Preferences Card -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <h3 class="text-sm font-bold text-[#0F172A] border-b border-slate-200 pb-3 flex items-center space-x-2">
                    <i class="fa-solid fa-sliders text-blue-600"></i>
                    <span>Buyer Preferences</span>
                </h3>

                <div class="space-y-3.5 text-xs">
                    <div class="space-y-0.5">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase">Property Type</div>
                        <div class="font-bold text-slate-900">{{ $lead->interested_unit_type ?? 'Luxury Apartments' }}</div>
                    </div>

                    <div class="space-y-0.5">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase">Bedrooms</div>
                        <div class="font-bold text-slate-900">3-4 BHK</div>
                    </div>

                    <div class="space-y-0.5">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase">Preferred Project</div>
                        <div class="font-bold text-slate-900">{{ $lead->project->name ?? 'All Projects' }}</div>
                    </div>

                    <div class="space-y-0.5">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase">Purchase Timeline</div>
                        <div class="font-bold text-slate-900">Immediate Buyer</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Edit Lead Details -->
<div id="editLeadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
    <div class="bg-white max-w-md w-full rounded-2xl p-5 border border-slate-200 shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-slate-200">
            <h3 class="text-sm font-bold text-[#0F172A]">Edit Lead Details</h3>
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

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('editLeadModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-lg border border-slate-200">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold rounded-lg shadow-xs">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Change Assigned Agent -->
<div id="changeAgentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
    <div class="bg-white max-w-md w-full rounded-2xl p-5 border border-slate-200 shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-slate-200">
            <h3 class="text-sm font-bold text-[#0F172A]">Re-assign Sales Executive</h3>
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

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('changeAgentModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-lg border border-slate-200">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold rounded-lg shadow-xs">Confirm Re-assignment</button>
            </div>
        </form>
    </div>
</div>
@endsection
