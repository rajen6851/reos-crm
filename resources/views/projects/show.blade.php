@extends('layouts.reos')

@section('title', "{$project->name} – Project Command Center – REOS")

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ 
    activeTab: 'inventory', 
    selectedTower: 'all', 
    selectedStatus: 'all', 
    selectedType: 'all', 
    searchQuery: '',
    unitModal: null 
}">
    <!-- Header Navigation & Action Bar -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-[#E2E8F0] shadow-2xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                    <span>›</span>
                    <a href="{{ route('projects.index') }}" class="hover:text-[#DC2626]">Projects Directory</a>
                    <span>›</span>
                    <span class="text-[#0F172A] font-bold">{{ $project->name }}</span>
                </div>
                <div class="flex items-center space-x-3">
                    <h1 class="page-heading text-2xl font-extrabold text-[#0F172A] tracking-tight">{{ $project->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-indigo-50 text-[#4F46E5] border border-indigo-200 uppercase">
                        {{ $project->code }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-[#059669] border border-emerald-200 uppercase">
                        {{ $project->project_type ?? 'Residential Enclave' }}
                    </span>
                </div>
                <p class="body-text text-xs text-[#64748B] mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span><i class="fa-solid fa-location-dot text-rose-500 mr-1"></i>Location: <strong class="text-[#0F172A]">{{ $project->city ?? 'Location N/A' }}</strong></span>
                    <span>•</span>
                    <span><i class="fa-solid fa-file-contract text-sky-600 mr-1"></i>RERA Registration: <strong class="font-mono text-[#0F172A]">{{ $project->rera_number ?? 'REG-APPROVED-2026' }}</strong></span>
                    <span>•</span>
                    <span><i class="fa-solid fa-eye text-slate-500 mr-1"></i>Visibility: <strong class="capitalize text-[#0F172A]">{{ $project->visibility ?? 'Public Showcase' }}</strong></span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button onclick="document.getElementById('sharePublicLinkModal').classList.remove('hidden')" class="px-4 py-2.5 bg-[#0F172A] hover:bg-slate-800 text-white btn-text text-xs rounded-xl shadow-2xs transition flex items-center space-x-2 cursor-pointer">
                    <i class="fa-solid fa-share-nodes text-white text-xs"></i>
                    <span>Share Showcase Link</span>
                </button>

                @can('manage-projects')
                <button onclick="document.getElementById('addTowerModal').classList.remove('hidden')" class="px-4 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text text-xs rounded-xl shadow-2xs transition flex items-center space-x-2 cursor-pointer">
                    <i class="fa-solid fa-building text-white text-xs"></i>
                    <span>+ Add Tower</span>
                </button>

                <button onclick="openAddUnitModal({{ json_encode($project) }})" class="px-4 py-2.5 bg-[#059669] hover:bg-[#047857] text-white btn-text text-xs rounded-xl shadow-2xs transition flex items-center space-x-2 cursor-pointer">
                    <i class="fa-solid fa-plus text-white text-xs"></i>
                    <span>+ Add Unit</span>
                </button>
                @endcan
            </div>
        </div>

        <!-- Inventory Summary Metrics Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-1 text-xs">
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-[#E2E8F0]">
                <span class="label-text text-[#64748B]">Total Towers</span>
                <div class="text-xl font-extrabold font-mono text-[#0F172A] mt-1">{{ $project->buildings->count() }} Towers</div>
            </div>

            <div class="p-3.5 bg-indigo-50/70 rounded-2xl border border-indigo-100">
                <span class="label-text text-indigo-700">Total Units</span>
                <div class="text-xl font-extrabold font-mono text-[#4F46E5] mt-1">{{ $project->units->count() }} Units</div>
            </div>

            <div class="p-3.5 bg-emerald-50/80 rounded-2xl border border-emerald-200">
                <span class="label-text text-emerald-800">Available Inventory</span>
                <div class="text-xl font-extrabold font-mono text-[#059669] mt-1">{{ $project->units->where('status', 'available')->count() }} Units</div>
            </div>

            <div class="p-3.5 bg-amber-50/80 rounded-2xl border border-amber-200">
                <span class="label-text text-amber-900">Hold / Reserved</span>
                <div class="text-xl font-extrabold font-mono text-amber-600 mt-1">{{ $project->units->where('status', 'hold')->count() }} Units</div>
            </div>

            <div class="p-3.5 bg-rose-50/80 rounded-2xl border border-rose-200">
                <span class="label-text text-rose-900">Booked / Sold</span>
                <div class="text-xl font-extrabold font-mono text-[#DC2626] mt-1">{{ $project->units->where('status', 'booked')->count() }} Units</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs Bar -->
    <div class="flex items-center space-x-2 bg-white p-2 rounded-2xl border border-[#E2E8F0] shadow-2xs overflow-x-auto text-xs font-bold">
        <button @click="activeTab = 'inventory'" :class="activeTab === 'inventory' ? 'bg-[#FEF2F2] text-[#DC2626] border-[#FEE2E2]' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2.5 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-layer-group"></i>
            <span>Inventory & Floor Matrix</span>
        </button>

        <button @click="activeTab = 'bookings'" :class="activeTab === 'bookings' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2.5 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-receipt text-indigo-600"></i>
            <span>Confirmed Bookings ({{ $recentBookings->count() }})</span>
        </button>

        <button @click="activeTab = 'leads'" :class="activeTab === 'leads' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2.5 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-user-plus text-emerald-600"></i>
            <span>Interested Leads ({{ $projectLeads->count() }})</span>
        </button>

        <button @click="activeTab = 'amenities'" :class="activeTab === 'amenities' ? 'bg-sky-50 text-sky-800 border-sky-200' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2.5 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-list-check text-sky-600"></i>
            <span>Project Specs & Amenities</span>
        </button>
    </div>

    <!-- TAB 1: Inventory & Floor Matrix -->
    <div x-show="activeTab === 'inventory'" class="space-y-6">
        <!-- Filter Controls Bar -->
        <div class="bg-white p-4 rounded-2xl border border-[#E2E8F0] shadow-2xs flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <!-- Search Input -->
                <div class="relative min-w-[200px] flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Search Unit No..." class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl pl-9 pr-3 py-2 text-xs font-semibold text-[#0F172A] focus:outline-none focus:border-[#059669]">
                </div>

                <!-- Tower Select -->
                <div>
                    <select x-model="selectedTower" class="bg-slate-50 border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs font-bold text-[#0F172A] focus:outline-none focus:border-[#059669]">
                        <option value="all">All Towers ({{ $project->buildings->count() }})</option>
                        @foreach($project->buildings as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Unit Type Select -->
                <div>
                    <select x-model="selectedType" class="bg-slate-50 border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs font-bold text-[#0F172A] focus:outline-none focus:border-[#059669]">
                        <option value="all">All Unit Types</option>
                        <option value="2BHK">2BHK</option>
                        <option value="3BHK">3BHK</option>
                        <option value="4BHK">4BHK / Villa</option>
                        <option value="Penthouse">Penthouse</option>
                        <option value="Plot">Plot</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <select x-model="selectedStatus" class="bg-slate-50 border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs font-bold text-[#0F172A] focus:outline-none focus:border-[#059669]">
                        <option value="all">All Statuses</option>
                        <option value="available">Available Only</option>
                        <option value="hold">Hold / Reserved Only</option>
                        <option value="booked">Booked / Sold Only</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Towers & Units Grid -->
        <div class="space-y-6">
            @forelse($project->buildings as $bldg)
            <div x-show="selectedTower === 'all' || selectedTower == '{{ $bldg->id }}'" class="bg-white rounded-3xl p-6 border border-[#E2E8F0] shadow-2xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-3">
                        <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-[#4F46E5] flex items-center justify-center font-bold text-lg border border-indigo-100">
                            <i class="fa-solid fa-building text-[#4F46E5]"></i>
                        </span>
                        <div>
                            <h2 class="text-base font-extrabold text-[#0F172A]">{{ $bldg->name }} ({{ $bldg->code }})</h2>
                            <p class="text-xs text-[#64748B] font-medium">{{ $bldg->total_floors }} Floors • {{ $bldg->units->count() }} Total Units</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 text-xs font-bold">
                        <span class="px-3 py-1 rounded-full bg-emerald-50 text-[#059669] border border-emerald-200">
                            {{ $bldg->units->where('status', 'available')->count() }} Available
                        </span>
                        <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                            {{ $bldg->units->where('status', 'hold')->count() }} Hold
                        </span>
                        <span class="px-3 py-1 rounded-full bg-rose-50 text-[#DC2626] border border-rose-200">
                            {{ $bldg->units->where('status', 'booked')->count() }} Booked
                        </span>
                    </div>
                </div>

                <!-- Units Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @forelse($bldg->units as $unit)
                    @php
                        $statusBadgeClass = match($unit->status) {
                            'available' => 'bg-emerald-50 border-emerald-300 text-emerald-950',
                            'hold' => 'bg-amber-50 border-amber-300 text-amber-950',
                            'booked' => 'bg-rose-50 border-rose-300 text-rose-950',
                            default => 'bg-slate-50 border-slate-200 text-slate-900'
                        };
                    @endphp
                    <div x-show="(selectedStatus === 'all' || selectedStatus === '{{ $unit->status }}') && 
                                (selectedType === 'all' || selectedType === '{{ $unit->unit_type }}') &&
                                (searchQuery === '' || '{{ $unit->unit_number }}'.toLowerCase().includes(searchQuery.toLowerCase()))"
                         class="p-3.5 rounded-2xl border transition-all duration-150 space-y-2 bg-white shadow-2xs hover:shadow-md cursor-pointer {{ $statusBadgeClass }}"
                         @click="unitModal = {{ json_encode($unit) }}">
                        
                        <div class="flex justify-between items-start">
                            <div class="text-xs font-extrabold text-[#0F172A]">Unit {{ $unit->unit_number }}</div>
                            <span class="text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-[#475569]">{{ $unit->unit_type }}</span>
                        </div>

                        <div class="text-xs font-mono font-extrabold text-[#059669]">₹{{ number_format($unit->final_price ?? $unit->price ?? 0) }}</div>
                        <div class="text-[10px] text-[#64748B] font-mono flex items-center justify-between">
                            <span>{{ $unit->carpet_area }} sqft</span>
                            <span class="font-bold uppercase text-[9px] px-1 py-0.2 rounded {{ $unit->status === 'available' ? 'bg-emerald-100 text-emerald-800' : ($unit->status === 'hold' ? 'bg-amber-100 text-amber-900' : 'bg-rose-100 text-rose-800') }}">
                                {{ $unit->status }}
                            </span>
                        </div>

                        @can('manage-projects')
                        <div class="pt-1.5 flex items-center justify-between gap-1 border-t border-slate-100" @click.stop>
                            <form action="{{ route('units.update-status', $unit->id) }}" method="POST" class="flex-1">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="w-full text-[10px] bg-slate-50 border border-slate-200 rounded p-1 font-bold text-slate-800 focus:outline-none">
                                    <option value="available" {{ $unit->status === 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="hold" {{ $unit->status === 'hold' ? 'selected' : '' }}>Hold</option>
                                    <option value="booked" {{ $unit->status === 'booked' ? 'selected' : '' }}>Booked</option>
                                </select>
                            </form>
                            
                            <button type="button" onclick="openEditUnitModal({{ json_encode($unit) }})" class="text-slate-500 hover:text-amber-600 text-xs p-0.5" title="Edit Unit Specs"><i class="fa-solid fa-pen-to-square"></i></button>

                            <form action="{{ route('units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('Delete Unit {{ $unit->unit_number }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 text-xs p-0.5" title="Delete Unit"><i class="fa-solid fa-trash-can text-rose-500"></i></button>
                            </form>
                        </div>
                        @endcan
                    </div>
                    @empty
                    <div class="col-span-full text-xs text-[#64748B] italic py-4 bg-slate-50 rounded-xl text-center border border-dashed border-[#CBD5E1]">
                        No units added in {{ $bldg->name }} yet. Click '+ Add Unit' above.
                    </div>
                    @endforelse
                </div>
            </div>
            @empty
            <div class="p-8 text-center bg-white rounded-3xl border border-[#E2E8F0] shadow-2xs space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 text-[#4F46E5] flex items-center justify-center text-xl border border-indigo-100">
                    <i class="fa-solid fa-building"></i>
                </div>
                <h3 class="text-base font-extrabold text-[#0F172A]">No Towers Created Yet</h3>
                <p class="text-xs text-[#64748B]">Create your first building tower for {{ $project->name }} to manage floor inventory.</p>
                @can('manage-projects')
                <button onclick="document.getElementById('addTowerModal').classList.remove('hidden')" class="px-5 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text text-xs rounded-xl shadow-xs inline-block cursor-pointer">
                    + Add Tower / Building
                </button>
                @endcan
            </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: Confirmed Bookings -->
    <div x-show="activeTab === 'bookings'" class="bg-white rounded-3xl p-6 border border-[#E2E8F0] shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-extrabold text-[#0F172A]">Confirmed Unit Bookings</h2>
                <p class="text-xs text-[#64748B]">Active customer bookings and agreements for {{ $project->name }}</p>
            </div>
            <a href="{{ route('bookings.index') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-[#0F172A] text-xs font-bold rounded-xl transition">
                View All Bookings Directory →
            </a>
        </div>

        @if($recentBookings->isEmpty())
            <div class="p-8 text-center text-xs text-[#64748B] italic bg-slate-50 rounded-2xl border border-dashed border-[#CBD5E1]">
                No unit bookings logged for {{ $project->name }} yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[#475569] font-extrabold uppercase border-b border-[#E2E8F0]">
                        <tr>
                            <th class="p-3">Booking Code</th>
                            <th class="p-3">Customer Details</th>
                            <th class="p-3">Unit Info</th>
                            <th class="p-3">Sales Executive</th>
                            <th class="p-3">Booking Cost</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($recentBookings as $b)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3 font-mono font-extrabold text-[#4F46E5]">{{ $b->booking_code }}</td>
                            <td class="p-3">
                                <div class="font-bold text-[#0F172A]">{{ $b->customer_name }}</div>
                                <div class="text-[11px] text-[#64748B]">{{ $b->customer_phone }}</div>
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-[#0F172A]">Unit {{ $b->unit->unit_number ?? 'N/A' }}</div>
                                <div class="text-[11px] text-[#64748B]">{{ $b->unit->unit_type ?? '' }}</div>
                            </td>
                            <td class="p-3 font-semibold text-[#0F172A]">{{ $b->salesUser->name ?? 'Direct Admin' }}</td>
                            <td class="p-3 font-mono font-bold text-[#059669]">₹{{ number_format($b->total_unit_cost ?? $b->booking_amount ?? 0) }}</td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-[#059669] border border-emerald-200">
                                    {{ $b->status ?? 'Confirmed' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- TAB 3: Interested Leads -->
    <div x-show="activeTab === 'leads'" class="bg-white rounded-3xl p-6 border border-[#E2E8F0] shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-extrabold text-[#0F172A]">Interested Customer Leads</h2>
                <p class="text-xs text-[#64748B]">Prospective buyers inquiring for {{ $project->name }}</p>
            </div>
            <a href="{{ route('leads.index') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-[#0F172A] text-xs font-bold rounded-xl transition">
                View All Leads Directory →
            </a>
        </div>

        @if($projectLeads->isEmpty())
            <div class="p-8 text-center text-xs text-[#64748B] italic bg-slate-50 rounded-2xl border border-dashed border-[#CBD5E1]">
                No customer inquiries logged for {{ $project->name }} yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[#475569] font-extrabold uppercase border-b border-[#E2E8F0]">
                        <tr>
                            <th class="p-3">Lead Name</th>
                            <th class="p-3">Contact Information</th>
                            <th class="p-3">Requirement</th>
                            <th class="p-3">Assigned Executive</th>
                            <th class="p-3">AI Score</th>
                            <th class="p-3">Pipeline Stage</th>
                            <th class="p-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($projectLeads as $lead)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3 font-bold text-[#0F172A]">{{ $lead->name }}</td>
                            <td class="p-3">
                                <div>{{ $lead->phone }}</div>
                                <div class="text-[11px] text-[#64748B]">{{ $lead->email ?? 'N/A' }}</div>
                            </td>
                            <td class="p-3">
                                <span class="font-bold text-[#0F172A]">{{ $lead->requirement_type ?? '2BHK' }}</span>
                            </td>
                            <td class="p-3 font-semibold text-[#0F172A]">{{ $lead->assignedTo->name ?? 'Unassigned' }}</td>
                            <td class="p-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-[#4F46E5] border border-indigo-200">
                                    {{ $lead->ai_score ?? 80 }}/100
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ str_replace('_', ' ', $lead->status) }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <a href="{{ route('leads.show', $lead->id) }}" class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold rounded-lg transition">
                                    Details →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- TAB 4: Project Specs & Amenities -->
    <div x-show="activeTab === 'amenities'" class="bg-white rounded-3xl p-6 border border-[#E2E8F0] shadow-2xs space-y-6">
        <div>
            <h2 class="text-base font-extrabold text-[#0F172A]">Project Specifications & Lifestyle Amenities</h2>
            <p class="text-xs text-[#64748B]">Architectural highlights, master plan infrastructure & buyer facilities</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div class="p-4 bg-slate-50 rounded-2xl border border-[#E2E8F0] space-y-2">
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-[#059669] flex items-center justify-center font-bold text-base border border-emerald-100">
                    <i class="fa-solid fa-dumbbell text-[#059669]"></i>
                </span>
                <div class="font-extrabold text-[#0F172A]">Clubhouse & Gym</div>
                <p class="text-[11px] text-[#64748B]">Fully equipped air-conditioned fitness suite & multi-purpose hall.</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-[#E2E8F0] space-y-2">
                <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center font-bold text-base border border-sky-100">
                    <i class="fa-solid fa-water-ladder text-sky-600"></i>
                </span>
                <div class="font-extrabold text-[#0F172A]">Swimming Pool</div>
                <p class="text-[11px] text-[#64748B]">Infinity pool with separate kids splash deck & sun loungers.</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-[#E2E8F0] space-y-2">
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-base border border-amber-100">
                    <i class="fa-solid fa-charging-station text-amber-600"></i>
                </span>
                <div class="font-extrabold text-[#0F172A]">EV Charging Bay</div>
                <p class="text-[11px] text-[#64748B]">Dedicated fast EV charging slots per basement level.</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-[#E2E8F0] space-y-2">
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-[#4F46E5] flex items-center justify-center font-bold text-base border border-indigo-100">
                    <i class="fa-solid fa-shield-halved text-[#4F46E5]"></i>
                </span>
                <div class="font-extrabold text-[#0F172A]">24x7 Smart Security</div>
                <p class="text-[11px] text-[#64748B]">CCTV surveillance, RFID boom barriers & biometric access.</p>
            </div>
        </div>

        <div class="p-5 bg-linear-to-r from-slate-900 to-slate-800 text-white rounded-2xl space-y-2 text-xs">
            <div class="font-extrabold text-sm text-emerald-400">RERA & Legal Verification Note</div>
            <p class="text-slate-300 leading-relaxed">
                Project {{ $project->name }} is approved under state Real Estate Regulatory Authority (RERA No: <strong>{{ $project->rera_number }}</strong>). All title deeds, encumbrance certificates, and building approval plans are verified.
            </p>
        </div>
    </div>

    <!-- Unit Detail Quick Drawer Modal (Triggered by Unit Card Click) -->
    <div x-show="unitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" style="display: none;">
        <div class="bg-white w-full max-w-md p-6 rounded-3xl space-y-4 border border-[#E2E8F0] shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-extrabold text-[#0F172A]">Unit <span x-text="unitModal?.unit_number"></span> Detailed Breakdown</h3>
                    <p class="text-xs text-[#64748B]" x-text="unitModal?.unit_type + ' Configuration'"></p>
                </div>
                <button @click="unitModal = null" class="text-slate-400 hover:text-slate-700 font-bold text-base cursor-pointer">✕</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-2xl border border-[#E2E8F0]">
                    <div>
                        <span class="label-text text-[#64748B]">Carpet Area</span>
                        <div class="font-extrabold font-mono text-[#0F172A] text-sm" x-text="unitModal?.carpet_area + ' sqft'"></div>
                    </div>
                    <div>
                        <span class="label-text text-[#64748B]">Super Builtup Area</span>
                        <div class="font-extrabold font-mono text-[#0F172A] text-sm" x-text="unitModal?.super_builtup_area || (unitModal?.carpet_area * 1.35) + ' sqft'"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 p-3 bg-emerald-50/80 rounded-2xl border border-emerald-200">
                    <div>
                        <span class="label-text text-emerald-800">Base Unit Rate</span>
                        <div class="font-extrabold font-mono text-emerald-950 text-sm" x-text="'₹' + Number(unitModal?.base_price || 0).toLocaleString()"></div>
                    </div>
                    <div>
                        <span class="label-text text-emerald-800">Final All-Inclusive Cost</span>
                        <div class="font-extrabold font-mono text-[#059669] text-base" x-text="'₹' + Number(unitModal?.final_price || unitModal?.price || 0).toLocaleString()"></div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl border border-[#E2E8F0]">
                    <span class="label-text text-[#64748B]">Current Availability Status</span>
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase"
                          :class="unitModal?.status === 'available' ? 'bg-emerald-100 text-emerald-800' : (unitModal?.status === 'hold' ? 'bg-amber-100 text-amber-900' : 'bg-rose-100 text-rose-800')"
                          x-text="unitModal?.status"></span>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                <button type="button" @click="unitModal = null" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-[#0F172A] btn-text text-xs rounded-xl cursor-pointer">Close</button>
                @can('manage-projects')
                <button type="button" @click="openEditUnitModal(unitModal); unitModal = null" class="px-5 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text text-xs rounded-xl cursor-pointer">Edit Specs</button>
                @endcan
            </div>
        </div>
    </div>

    <!-- Add Tower / Building Modal -->
    <div id="addTowerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-md p-6 rounded-3xl space-y-4 border border-[#E2E8F0] shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-[#0F172A]">Add Building Tower</h3>
                <button onclick="document.getElementById('addTowerModal').classList.add('hidden')" class="text-slate-400 font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('projects.store-building', $project->id) }}" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Tower Name *</label>
                        <input type="text" name="name" required placeholder="Tower A / Block 1" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Tower Code *</label>
                        <input type="text" name="code" required placeholder="TA" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] uppercase font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                </div>

                <div>
                    <label class="block text-[#475569] mb-1 font-bold">Total Floors *</label>
                    <input type="number" name="total_floors" required min="1" value="10" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('addTowerModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-[#0F172A] font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#4F46E5] hover:bg-[#4338CA] text-white font-bold rounded-xl shadow-xs">Add Tower</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Unit Modal -->
    <div id="addUnitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-lg p-6 rounded-3xl space-y-4 border border-[#E2E8F0] shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-[#0F172A]">Add Inventory Unit</h3>
                <button onclick="document.getElementById('addUnitModal').classList.add('hidden')" class="text-slate-400 font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('projects.store-unit', $project->id) }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[#475569] mb-1 font-bold">Select Tower / Building *</label>
                    <select name="building_id" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-bold focus:outline-none focus:border-[#4F46E5]">
                        @foreach($project->buildings as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Unit No. *</label>
                        <input type="text" name="unit_number" required placeholder="101" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Floor No.</label>
                        <input type="number" name="floor_number" value="1" min="1" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Unit Type *</label>
                        <select name="unit_type" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-bold focus:outline-none focus:border-[#4F46E5]">
                            <option value="2BHK">2BHK</option>
                            <option value="3BHK">3BHK</option>
                            <option value="4BHK">4BHK / Villa</option>
                            <option value="Penthouse">Penthouse</option>
                            <option value="Plot">Plot</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Carpet Area (sqft) *</label>
                        <input type="number" name="carpet_area" required value="1250" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Base Price (₹) *</label>
                        <input type="number" name="base_price" required value="7500000" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Final Price (₹) *</label>
                        <input type="number" name="final_price" required value="8200000" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('addUnitModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-[#0F172A] font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#059669] hover:bg-[#047857] text-white font-bold rounded-xl shadow-xs">Save Unit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Unit Modal -->
    <div id="editUnitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-lg p-6 rounded-3xl space-y-4 border border-[#E2E8F0] shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-[#0F172A]">Edit Inventory Unit Specs</h3>
                <button onclick="document.getElementById('editUnitModal').classList.add('hidden')" class="text-slate-400 font-bold">✕</button>
            </div>

            <form id="editUnitForm" method="POST" action="" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Unit No. *</label>
                        <input type="text" id="edit_unit_number" name="unit_number" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Unit Type *</label>
                        <select id="edit_unit_type" name="unit_type" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-bold focus:outline-none focus:border-[#4F46E5]">
                            <option value="2BHK">2BHK</option>
                            <option value="3BHK">3BHK</option>
                            <option value="4BHK">4BHK / Villa</option>
                            <option value="Penthouse">Penthouse</option>
                            <option value="Plot">Plot</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Carpet Area (sqft) *</label>
                        <input type="number" id="edit_carpet_area" name="carpet_area" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Base Price (₹) *</label>
                        <input type="number" id="edit_base_price" name="base_price" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                    <div>
                        <label class="block text-[#475569] mb-1 font-bold">Final Price (₹) *</label>
                        <input type="number" id="edit_final_price" name="final_price" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-[#0F172A] font-mono font-bold focus:outline-none focus:border-[#4F46E5]">
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('editUnitModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-[#0F172A] font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-xs">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Share Public Link Modal -->
<div id="sharePublicLinkModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
    <div class="bg-white w-full max-w-md p-6 rounded-3xl space-y-4 border border-[#E2E8F0] shadow-xl">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-link text-[#4F46E5]"></i>
                <h3 class="text-base font-extrabold text-[#0F172A]">Share Public Showcase Link</h3>
            </div>
            <button onclick="document.getElementById('sharePublicLinkModal').classList.add('hidden')" class="text-slate-400 font-bold hover:text-slate-700">✕</button>
        </div>

        <p class="text-xs text-[#64748B]">
            This public link allows prospective buyers to view basic project details, towers, configurations, and submit direct callback inquiries.
        </p>

        @php
            $publicUrl = route('projects.public', $project->id);
            if(auth()->user()->isBroker()) {
                $brokerObj = \App\Models\Broker::where('user_id', auth()->id())->first();
                if($brokerObj) {
                    $publicUrl .= '?ref=' . $brokerObj->id;
                }
            }
        @endphp

        <div class="space-y-2">
            <label class="block text-xs font-bold text-[#475569]">Public Link URL</label>
            <div class="flex items-center space-x-2">
                <input type="text" readonly id="publicProjectUrlInput" value="{{ $publicUrl }}" 
                       class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl p-2.5 text-xs text-[#0F172A] font-mono focus:outline-none">
                <button onclick="copyProjectLinkModal()" class="px-4 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white font-bold text-xs rounded-xl shadow-2xs whitespace-nowrap transition cursor-pointer">
                    <span id="modalCopyBtnText">Copy</span>
                </button>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
            <a href="{{ $publicUrl }}" target="_blank" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-[#0F172A] text-center font-bold text-xs rounded-xl transition">
                <i class="fa-solid fa-eye mr-1"></i>Preview Link
            </a>
            <button onclick="shareWhatsAppModal()" class="flex-1 py-2.5 bg-[#059669] hover:bg-[#047857] text-white text-center font-bold text-xs rounded-xl transition shadow-2xs cursor-pointer">
                <i class="fa-brands fa-whatsapp text-white mr-1"></i>WhatsApp Share
            </button>
        </div>
    </div>
</div>

<script>
    function openAddUnitModal(project) {
        var buildings = project.buildings || [];
        if (buildings.length === 0) {
            alert('Please create a Tower first by clicking "+ Add Tower" before adding units.');
            return;
        }
        document.getElementById('addUnitModal').classList.remove('hidden');
    }

    function openEditUnitModal(unit) {
        document.getElementById('editUnitForm').action = "/units/" + unit.id;
        document.getElementById('edit_unit_number').value = unit.unit_number || '';
        document.getElementById('edit_unit_type').value = unit.unit_type || '2BHK';
        document.getElementById('edit_carpet_area').value = unit.carpet_area || '';
        document.getElementById('edit_base_price').value = unit.base_price || '';
        document.getElementById('edit_final_price').value = unit.final_price || unit.price || '';
        document.getElementById('editUnitModal').classList.remove('hidden');
    }

    function copyProjectLinkModal() {
        const input = document.getElementById('publicProjectUrlInput');
        navigator.clipboard.writeText(input.value).then(() => {
            const btnText = document.getElementById('modalCopyBtnText');
            btnText.innerText = 'Copied!';
            setTimeout(() => { btnText.innerText = 'Copy'; }, 2500);
        });
    }

    function shareWhatsAppModal() {
        const url = encodeURIComponent(document.getElementById('publicProjectUrlInput').value);
        const text = encodeURIComponent("Check out {{ $project->name }} in {{ $project->city }}!\nDetails & Pricing link: ");
        window.open(`https://api.whatsapp.com/send?text=${text}${url}`, '_blank');
    }
</script>
@endsection
