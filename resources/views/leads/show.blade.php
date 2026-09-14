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

            @can('assign-leads')
            {{-- Transfer Lead Button (Manager/Admin only) --}}
            <button onclick="document.getElementById('transferLeadModal').classList.remove('hidden')" class="px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 font-bold text-xs rounded-lg shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-arrows-left-right text-amber-600 text-xs"></i>
                <span>Transfer Lead</span>
                @if($lead->transfer_count > 0)
                <span class="px-1.5 py-0.5 rounded-full bg-amber-500 text-white text-[9px] font-mono font-bold">{{ $lead->transfer_count }}x</span>
                @endif
            </button>
            @endcan

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

            <!-- Call Logs & Recordings Card -->
            @if($lead->calls->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <h3 class="text-base font-bold text-[#0F172A] flex items-center space-x-2 border-b border-slate-200 pb-3">
                    <i class="fa-solid fa-phone-volume text-emerald-600"></i>
                    <span>Call Logs & Recordings</span>
                    <span class="ml-auto px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-mono font-bold">{{ $lead->calls->count() }} calls</span>
                </h3>

                <div class="space-y-3 text-xs">
                    @foreach($lead->calls as $call)
                    <div class="p-4 rounded-xl border {{ $call->audio_recording_path ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-200' }} space-y-2.5">
                        <!-- Call Header -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-7 h-7 rounded-lg {{ $call->audio_recording_path ? 'bg-emerald-500' : 'bg-slate-300' }} flex items-center justify-center shrink-0">
                                    <i class="fa-solid {{ $call->audio_recording_path ? 'fa-microphone' : 'fa-phone' }} text-white text-xs"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $call->user->name ?? 'Executive' }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">{{ $call->called_at->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $call->call_outcome === 'connected' ? 'bg-emerald-100 text-emerald-700 border border-emerald-300' : '' }}
                                {{ $call->call_outcome === 'not_connected' ? 'bg-rose-100 text-rose-700 border border-rose-300' : '' }}
                                {{ $call->call_outcome === 'callback_required' ? 'bg-amber-100 text-amber-700 border border-amber-300' : '' }}
                                {{ $call->call_outcome === 'busy' ? 'bg-orange-100 text-orange-700 border border-orange-300' : '' }}
                                {{ !in_array($call->call_outcome, ['connected','not_connected','callback_required','busy']) ? 'bg-slate-100 text-slate-700 border border-slate-300' : '' }}
                            ">
                                {{ ucwords(str_replace('_', ' ', $call->call_outcome)) }}
                            </span>
                        </div>

                        <!-- Notes -->
                        @if($call->notes)
                        <p class="text-slate-600 font-medium leading-relaxed pl-9">{{ $call->notes }}</p>
                        @endif

                        <!-- Audio Player -->
                        @if($call->audio_recording_path)
                        <div class="pl-9 space-y-1.5">
                            <div class="flex items-center space-x-1.5 text-[10px] text-emerald-700 font-semibold">
                                <i class="fa-solid fa-music"></i>
                                <span>{{ $call->audio_recording_name ?? 'Call Recording' }}</span>
                            </div>
                            <audio controls preload="metadata"
                                   src="{{ asset('storage/' . $call->audio_recording_path) }}"
                                   class="w-full rounded-lg"
                                   style="height:36px">
                                Your browser does not support audio playback.
                            </audio>
                            <a href="{{ asset('storage/' . $call->audio_recording_path) }}"
                               download="{{ $call->audio_recording_name ?? 'recording' }}"
                               class="inline-flex items-center space-x-1 text-[10px] text-emerald-600 hover:text-emerald-800 font-semibold">
                                <i class="fa-solid fa-download text-[9px]"></i>
                                <span>Download Recording</span>
                            </a>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
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

                {{-- Transfer count badge --}}
                @if($lead->transfer_count > 0)
                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-amber-50 border border-amber-200 text-xs">
                    <span class="text-amber-800 font-semibold">Transfer History</span>
                    <span class="font-bold font-mono text-amber-700">{{ $lead->transfer_count }} transfer{{ $lead->transfer_count > 1 ? 's' : '' }}</span>
                </div>
                @endif
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

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL 3: TRANSFER LEAD (Manager/Admin only — with reason + note)           --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div id="transferLeadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
    <div class="bg-white max-w-lg w-full rounded-2xl border border-slate-200 shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="flex justify-between items-center px-5 py-4 border-b border-slate-200 bg-amber-50">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center">
                    <i class="fa-solid fa-arrows-left-right text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[#0F172A]">Transfer Lead to Another Executive</h3>
                    <p class="text-[10px] text-slate-500 font-medium">{{ $lead->lead_code }} &bull; {{ $lead->first_name }} {{ $lead->last_name }}</p>
                </div>
            </div>
            <button onclick="document.getElementById('transferLeadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 font-bold text-lg">&times;</button>
        </div>

        <form action="{{ route('leads.transfer', $lead->id) }}" method="POST" class="p-5 space-y-4 text-xs">
            @csrf

            {{-- Current Executive Info --}}
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-full bg-[#0F172A] text-white font-bold text-xs flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($lead->assignedTo->name ?? 'UN', 0, 2)) }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-900">{{ $lead->assignedTo->name ?? 'Unassigned' }}</div>
                        <div class="text-[10px] text-slate-500">Current Assignee</div>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-right text-slate-400"></i>
                <div class="text-slate-400 font-semibold text-[11px]">Select New &rarr;</div>
            </div>

            {{-- Select New Executive --}}
            <div class="space-y-1.5">
                <label class="form-label font-bold text-slate-800">Transfer To <span class="text-rose-500">*</span></label>
                <select name="new_assignee_id" required class="form-input font-semibold">
                    <option value="">— Select Sales Executive —</option>
                    @foreach(\App\Models\User::where('company_id', $lead->company_id)->where('id', '!=', $lead->assigned_to_user_id)->whereHas('role', function($q){ $q->whereIn('slug', ['sales_executive', 'executive', 'sales']); })->orderBy('name')->get() as $exec)
                    <option value="{{ $exec->id }}">{{ $exec->name }} ({{ $exec->role->name ?? 'Executive' }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Transfer Reason (Required) --}}
            <div class="space-y-1.5">
                <label class="form-label font-bold text-slate-800">Transfer Reason <span class="text-rose-500">*</span></label>
                <select name="transfer_reason" required class="form-input">
                    <option value="">— Select Reason —</option>
                    <option value="No response from current executive">No Response from Current Executive</option>
                    <option value="Executive on leave / unavailable">Executive on Leave / Unavailable</option>
                    <option value="Executive overloaded — workload balancing">Executive Overloaded — Workload Balancing</option>
                    <option value="Customer requested different executive">Customer Requested Different Executive</option>
                    <option value="Executive resigned / left company">Executive Resigned / Left Company</option>
                    <option value="Geographic re-routing">Geographic Re-Routing</option>
                    <option value="Manager decision">Manager Decision</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            {{-- Optional Note --}}
            <div class="space-y-1.5">
                <label class="form-label">Additional Notes <span class="text-slate-400">(optional)</span></label>
                <textarea name="transfer_note" rows="2" placeholder="Add context or special instructions for the new executive..." class="form-input resize-none"></textarea>
            </div>

            {{-- Transfer Guard Warning --}}
            @if($lead->transfer_count >= 2)
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 font-semibold flex items-start space-x-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-500 mt-0.5"></i>
                <span>This lead has been transferred <strong>{{ $lead->transfer_count }} times</strong> already. Maximum 5 transfers allowed. Please consider escalating to Director instead.</span>
            </div>
            @endif

            {{-- Actions --}}
            <div class="flex justify-end space-x-2 pt-2 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('transferLeadModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg border border-slate-200 text-xs transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg shadow-xs text-xs transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-arrows-left-right text-xs"></i>
                    <span>Transfer &amp; Notify Both Executives</span>
                </button>
            </div>
        </form>
    </div>
</div>
