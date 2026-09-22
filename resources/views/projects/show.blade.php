@extends('layouts.reos')

@section('title', "{$project->name} â€“ Project Command Center â€“ UrbanProperty")

@section('content')
<div class="space-y-5 max-w-7xl mx-auto pb-12" x-data="{ 
    activeTab: 'inventory', 
    selectedTower: 'all', 
    selectedStatus: 'all', 
    selectedType: 'all', 
    searchQuery: '',
    unitModal: null 
}">
    <!-- Header Navigation & Action Bar -->
    <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <!-- Breadcrumbs -->
                <nav class="flex items-center space-x-2 text-xs text-slate-500 mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 font-medium transition">Dashboard</a>
                    <span class="text-slate-300">/</span>
                    <a href="{{ route('projects.index') }}" class="hover:text-emerald-700 font-medium transition">Projects Directory</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-900 font-semibold truncate">{{ $project->name }}</span>
                </nav>
                
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $project->name }}</h1>
                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200 uppercase">
                        {{ $project->code }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                        {{ $project->project_type ?? 'Residential Enclave' }}
                    </span>
                </div>

                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-600">
                    <span class="inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-rose-500"></i>
                        <span>Location: <strong class="text-slate-900 font-medium">{{ $project->city ?? 'Location N/A' }}</strong></span>
                    </span>
                    <span class="text-slate-300">â€¢</span>
                    <span class="inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-file-contract text-emerald-600"></i>
                        <span>RERA Registration: <strong class="font-mono text-slate-900 font-medium">{{ $project->rera_number ?? 'REG-APPROVED-2026' }}</strong></span>
                    </span>
                    <span class="text-slate-300">â€¢</span>
                    <span class="inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-eye text-slate-400"></i>
                        <span>Visibility: <strong class="capitalize text-slate-900 font-medium">{{ $project->visibility ?? 'Public Showcase' }}</strong></span>
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button onclick="document.getElementById('sharePublicLinkModal').classList.remove('hidden')" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md border border-slate-200 transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-share-nodes text-slate-500"></i>
                    <span>Share Link</span>
                </button>

                @can('manage-projects')
                <button onclick="document.getElementById('addTowerModal').classList.remove('hidden')" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-md transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-building text-slate-300 text-xs"></i>
                    <span>+ Add Tower</span>
                </button>

                <button onclick="openAddUnitModal({{ json_encode($project) }})" class="px-3.5 py-2 bg-[#047857] hover:bg-[#065f46] text-white text-xs font-semibold rounded-md transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>+ Add Unit</span>
                </button>
                @endcan
            </div>
        </div>

        <!-- Inventory Summary Metrics Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-1 text-xs">
            <div class="p-3 bg-slate-50/70 rounded-md border border-slate-200">
                <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Total Towers</span>
                <div class="text-lg font-bold font-mono text-slate-900 mt-0.5">{{ $project->buildings->count() }} <span class="text-xs font-normal text-slate-500">Towers</span></div>
            </div>

            <div class="p-3 bg-indigo-50/40 rounded-md border border-indigo-100">
                <span class="text-[11px] font-medium text-indigo-700 uppercase tracking-wider">Total Inventory</span>
                <div class="text-lg font-bold font-mono text-indigo-900 mt-0.5">{{ $project->units->count() }} <span class="text-xs font-normal text-indigo-600">Units</span></div>
            </div>

            <div class="p-3 bg-emerald-50/50 rounded-md border border-emerald-200">
                <span class="text-[11px] font-medium text-emerald-800 uppercase tracking-wider">Available</span>
                <div class="text-lg font-bold font-mono text-emerald-700 mt-0.5">{{ $project->units->where('status', 'available')->count() }} <span class="text-xs font-normal text-emerald-600">Units</span></div>
            </div>

            <div class="p-3 bg-amber-50/50 rounded-md border border-amber-200">
                <span class="text-[11px] font-medium text-amber-800 uppercase tracking-wider">Hold / Reserved</span>
                <div class="text-lg font-bold font-mono text-amber-700 mt-0.5">{{ $project->units->where('status', 'hold')->count() }} <span class="text-xs font-normal text-amber-600">Units</span></div>
            </div>

            <div class="p-3 bg-rose-50/50 rounded-md border border-rose-200">
                <span class="text-[11px] font-medium text-rose-800 uppercase tracking-wider">Booked / Sold</span>
                <div class="text-lg font-bold font-mono text-rose-700 mt-0.5">{{ $project->units->where('status', 'booked')->count() }} <span class="text-xs font-normal text-rose-600">Units</span></div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs Bar -->
    <div class="bg-white rounded-lg border border-slate-200 p-1 flex items-center space-x-1 text-xs font-semibold shadow-xs">
        <button @click="activeTab = 'inventory'" :class="activeTab === 'inventory' ? 'bg-emerald-50 text-[#047857] border-emerald-300 font-bold' : 'text-slate-600 border-transparent hover:bg-slate-50'" class="px-3.5 py-2 rounded-md border transition flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-layer-group text-slate-500" :class="activeTab === 'inventory' ? 'text-[#047857]' : ''"></i>
            <span>Inventory & Floor Matrix</span>
        </button>

        <button @click="activeTab = 'bookings'" :class="activeTab === 'bookings' ? 'bg-emerald-50 text-[#047857] border-emerald-300 font-bold' : 'text-slate-600 border-transparent hover:bg-slate-50'" class="px-3.5 py-2 rounded-md border transition flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-receipt text-slate-500" :class="activeTab === 'bookings' ? 'text-[#047857]' : ''"></i>
            <span>Confirmed Bookings ({{ $recentBookings->count() }})</span>
        </button>

        <button @click="activeTab = 'leads'" :class="activeTab === 'leads' ? 'bg-emerald-50 text-[#047857] border-emerald-300 font-bold' : 'text-slate-600 border-transparent hover:bg-slate-50'" class="px-3.5 py-2 rounded-md border transition flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-user-plus text-slate-500" :class="activeTab === 'leads' ? 'text-[#047857]' : ''"></i>
            <span>Interested Leads ({{ $projectLeads->count() }})</span>
        </button>

        <button @click="activeTab = 'amenities'" :class="activeTab === 'amenities' ? 'bg-emerald-50 text-[#047857] border-emerald-300 font-bold' : 'text-slate-600 border-transparent hover:bg-slate-50'" class="px-3.5 py-2 rounded-md border transition flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-list-check text-slate-500" :class="activeTab === 'amenities' ? 'text-[#047857]' : ''"></i>
            <span>Specs & Amenities</span>
        </button>
    </div>

    <!-- TAB 1: Inventory & Floor Matrix -->
    <div x-show="activeTab === 'inventory'" class="space-y-4">
        <!-- Filter Controls Bar -->
        <div class="bg-white p-3.5 rounded-lg border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <!-- Search Input -->
                <div class="relative min-w-[200px] flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Filter Unit Number..." class="w-full bg-slate-50 border border-slate-300 rounded-md pl-8 pr-3 py-1.5 text-xs text-slate-900 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                </div>

                <!-- Tower Select -->
                <div>
                    <select x-model="selectedTower" class="bg-slate-50 border border-slate-300 rounded-md px-3 py-1.5 text-xs font-medium text-slate-800 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                        <option value="all">All Towers ({{ $project->buildings->count() }})</option>
                        @foreach($project->buildings as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Unit Type Select -->
                <div>
                    <select x-model="selectedType" class="bg-slate-50 border border-slate-300 rounded-md px-3 py-1.5 text-xs font-medium text-slate-800 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
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
                    <select x-model="selectedStatus" class="bg-slate-50 border border-slate-300 rounded-md px-3 py-1.5 text-xs font-medium text-slate-800 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                        <option value="all">All Statuses</option>
                        <option value="available">Available Only</option>
                        <option value="hold">Hold / Reserved Only</option>
                        <option value="booked">Booked / Sold Only</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Towers & Units Grid -->
        <div class="space-y-4">
            @forelse($project->buildings as $bldg)
            <div x-show="selectedTower === 'all' || selectedTower == '{{ $bldg->id }}'" class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-3.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-3">
                        <span class="w-9 h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm border border-slate-200">
                            <i class="fa-solid fa-building"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">{{ $bldg->name }} <span class="text-xs text-slate-500 font-normal">({{ $bldg->code }})</span></h2>
                            <p class="text-xs text-slate-500">{{ $bldg->total_floors }} Floors â€¢ {{ $bldg->units->count() }} Total Units</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 text-[11px] font-medium">
                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200">
                            {{ $bldg->units->where('status', 'available')->count() }} Available
                        </span>
                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200">
                            {{ $bldg->units->where('status', 'hold')->count() }} Hold
                        </span>
                        <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-800 border border-rose-200">
                            {{ $bldg->units->where('status', 'booked')->count() }} Booked
                        </span>
                    </div>
                </div>

                <!-- Units Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5">
                    @forelse($bldg->units as $unit)
                    @php
                        $statusBorder = match($unit->status) {
                            'available' => 'border-l-4 border-l-emerald-600 bg-white hover:border-emerald-700',
                            'hold' => 'border-l-4 border-l-amber-500 bg-white hover:border-amber-600',
                            'booked' => 'border-l-4 border-l-rose-600 bg-slate-50/70 hover:border-rose-700',
                            default => 'border-l-4 border-l-slate-400 bg-white'
                        };
                    @endphp
                    <div x-show="(selectedStatus === 'all' || selectedStatus === '{{ $unit->status }}') && 
                                (selectedType === 'all' || selectedType === '{{ $unit->unit_type }}') &&
                                (searchQuery === '' || '{{ $unit->unit_number }}'.toLowerCase().includes(searchQuery.toLowerCase()))"
                         class="p-3 rounded-md border border-slate-200 transition-all space-y-1.5 shadow-2xs hover:shadow-xs cursor-pointer {{ $statusBorder }}"
                         @click="unitModal = {{ json_encode($unit) }}">
                        
                        <div class="flex justify-between items-start">
                            <div class="text-xs font-bold text-slate-900">Unit {{ $unit->unit_number }}</div>
                            <span class="text-[10px] font-semibold uppercase px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 border border-slate-200">{{ $unit->unit_type }}</span>
                        </div>

                        <div class="text-xs font-mono font-bold text-slate-900">â‚¹{{ number_format($unit->final_price ?? $unit->price ?? 0) }}</div>
                        <div class="text-[10px] text-slate-500 font-mono flex items-center justify-between">
                            <span>{{ $unit->carpet_area }} sqft</span>
                            <span class="font-semibold uppercase text-[9px] px-1 py-0.2 rounded {{ $unit->status === 'available' ? 'bg-emerald-100 text-emerald-800' : ($unit->status === 'hold' ? 'bg-amber-100 text-amber-900' : 'bg-rose-100 text-rose-800') }}">
                                {{ $unit->status }}
                            </span>
                        </div>

                        @can('manage-projects')
                        <div class="pt-1.5 flex items-center justify-between gap-1 border-t border-slate-100" @click.stop>
                            <form action="{{ route('units.update-status', $unit->id) }}" method="POST" class="flex-1">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="w-full text-[10px] bg-slate-50 border border-slate-200 rounded px-1 py-0.5 font-medium text-slate-800 focus:outline-none">
                                    <option value="available" {{ $unit->status === 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="hold" {{ $unit->status === 'hold' ? 'selected' : '' }}>Hold</option>
                                    <option value="booked" {{ $unit->status === 'booked' ? 'selected' : '' }}>Booked</option>
                                </select>
                            </form>
                            
                            <button type="button" onclick="openEditUnitModal({{ json_encode($unit) }})" class="text-slate-400 hover:text-amber-600 text-xs p-0.5" title="Edit Unit Specs"><i class="fa-solid fa-pen-to-square"></i></button>

                            <form action="{{ route('units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('Delete Unit {{ $unit->unit_number }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 text-xs p-0.5" title="Delete Unit"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </div>
                        @endcan
                    </div>
                    @empty
                    <div class="col-span-full text-xs text-slate-500 italic py-4 bg-slate-50 rounded-md text-center border border-dashed border-slate-300">
                        No units added in {{ $bldg->name }} yet. Click '+ Add Unit' above.
                    </div>
                    @endforelse
                </div>
            </div>
            @empty
            <div class="p-8 text-center bg-white rounded-lg border border-slate-200 shadow-xs space-y-3">
                <div class="w-10 h-10 mx-auto rounded-md bg-slate-100 text-slate-600 flex items-center justify-center text-lg border border-slate-200">
                    <i class="fa-solid fa-building"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">No Building Towers Configured</h3>
                <p class="text-xs text-slate-500">Create your first building tower for {{ $project->name }} to begin inventory mapping.</p>
                @can('manage-projects')
                <button onclick="document.getElementById('addTowerModal').classList.remove('hidden')" class="px-4 py-2 bg-[#047857] hover:bg-[#065f46] text-white text-xs font-semibold rounded-md shadow-xs inline-block cursor-pointer">
                    + Add Building Tower
                </button>
                @endcan
            </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: Confirmed Bookings -->
    <div x-show="activeTab === 'bookings'" class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Confirmed Unit Bookings</h2>
                <p class="text-xs text-slate-500">Active customer bookings and sales agreements for {{ $project->name }}</p>
            </div>
            <a href="{{ route('bookings.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md transition border border-slate-200">
                View All Bookings â†’
            </a>
        </div>

        @if($recentBookings->isEmpty())
            <div class="p-8 text-center text-xs text-slate-500 italic bg-slate-50">
                No unit bookings logged for {{ $project->name }} yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200 text-[11px]">
                        <tr>
                            <th class="p-3">Booking Code</th>
                            <th class="p-3">Customer Details</th>
                            <th class="p-3">Unit Info</th>
                            <th class="p-3">Sales Executive</th>
                            <th class="p-3">Booking Cost</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @foreach($recentBookings as $b)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3 font-mono font-bold text-slate-900">{{ $b->booking_code }}</td>
                            <td class="p-3">
                                <div class="font-semibold text-slate-900">{{ $b->customer_name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $b->customer_phone }}</div>
                            </td>
                            <td class="p-3">
                                <div class="font-semibold text-slate-900">Unit {{ $b->unit->unit_number ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $b->unit->unit_type ?? '' }}</div>
                            </td>
                            <td class="p-3 font-medium text-slate-800">{{ $b->salesUser->name ?? 'Direct Admin' }}</td>
                            <td class="p-3 font-mono font-semibold text-emerald-700">â‚¹{{ number_format($b->total_unit_cost ?? $b->booking_amount ?? 0) }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-emerald-50 text-emerald-800 border border-emerald-200">
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
    <div x-show="activeTab === 'leads'" class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Interested Customer Leads</h2>
                <p class="text-xs text-slate-500">Prospective buyers inquiring for {{ $project->name }}</p>
            </div>
            <a href="{{ route('leads.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md transition border border-slate-200">
                View All Leads â†’
            </a>
        </div>

        @if($projectLeads->isEmpty())
            <div class="p-8 text-center text-xs text-slate-500 italic bg-slate-50">
                No customer inquiries logged for {{ $project->name }} yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200 text-[11px]">
                        <tr>
                            <th class="p-3">Lead Name</th>
                            <th class="p-3">Contact Info</th>
                            <th class="p-3">Requirement</th>
                            <th class="p-3">Assigned Executive</th>
                            <th class="p-3">Score</th>
                            <th class="p-3">Stage</th>
                            <th class="p-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @foreach($projectLeads as $lead)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3 font-semibold text-slate-900">{{ $lead->name }}</td>
                            <td class="p-3">
                                <div class="font-mono text-slate-900">{{ $lead->phone }}</div>
                                <div class="text-[11px] text-slate-500">{{ $lead->email ?? 'N/A' }}</div>
                            </td>
                            <td class="p-3">
                                <span class="font-medium text-slate-900">{{ $lead->requirement_type ?? '2BHK' }}</span>
                            </td>
                            <td class="p-3 text-slate-700">{{ $lead->assignedTo->name ?? 'Unassigned' }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $lead->ai_score ?? 80 }}/100
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ str_replace('_', ' ', $lead->status) }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <a href="{{ route('leads.show', $lead->id) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-900 text-white text-[11px] font-semibold rounded transition">
                                    View â†’
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
    <div x-show="activeTab === 'amenities'" class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-5">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Project Specifications & Amenities</h2>
            <p class="text-xs text-slate-500">Architectural highlights, master plan infrastructure & buyer facilities</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="p-3.5 bg-slate-50 rounded-md border border-slate-200 space-y-1.5">
                <span class="w-7 h-7 rounded bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs border border-emerald-200">
                    <i class="fa-solid fa-dumbbell"></i>
                </span>
                <div class="font-bold text-slate-900">Clubhouse & Gym</div>
                <p class="text-[11px] text-slate-500 leading-relaxed">Fully equipped air-conditioned fitness suite & multi-purpose hall.</p>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-md border border-slate-200 space-y-1.5">
                <span class="w-7 h-7 rounded bg-sky-50 text-sky-700 flex items-center justify-center font-bold text-xs border border-sky-200">
                    <i class="fa-solid fa-water-ladder"></i>
                </span>
                <div class="font-bold text-slate-900">Swimming Pool</div>
                <p class="text-[11px] text-slate-500 leading-relaxed">Infinity pool with separate kids splash deck & sun loungers.</p>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-md border border-slate-200 space-y-1.5">
                <span class="w-7 h-7 rounded bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-xs border border-amber-200">
                    <i class="fa-solid fa-charging-station"></i>
                </span>
                <div class="font-bold text-slate-900">EV Charging Bay</div>
                <p class="text-[11px] text-slate-500 leading-relaxed">Dedicated fast EV charging slots per basement level.</p>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-md border border-slate-200 space-y-1.5">
                <span class="w-7 h-7 rounded bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs border border-slate-200">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                <div class="font-bold text-slate-900">24x7 Smart Security</div>
                <p class="text-[11px] text-slate-500 leading-relaxed">CCTV surveillance, RFID boom barriers & biometric access.</p>
            </div>
        </div>

        <div class="p-4 bg-slate-900 text-white rounded-md space-y-1 text-xs border border-slate-800">
            <div class="font-bold text-xs text-emerald-400">RERA & Legal Compliance Note</div>
            <p class="text-slate-300 leading-relaxed">
                Project {{ $project->name }} is officially registered under state Real Estate Regulatory Authority (RERA Registration: <strong class="font-mono text-white">{{ $project->rera_number }}</strong>). All title verification, clear property ownership, and approved municipal master plans are fully validated.
            </p>
        </div>
    </div>

    <!-- Unit Detail Quick Modal -->
    <div x-show="unitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4" style="display: none;">
        <div class="bg-white w-full max-w-md p-5 rounded-lg space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Unit <span x-text="unitModal?.unit_number"></span> Details</h3>
                    <p class="text-xs text-slate-500" x-text="unitModal?.unit_type + ' Configuration'"></p>
                </div>
                <button @click="unitModal = null" class="text-slate-400 hover:text-slate-600 font-bold text-base cursor-pointer">âœ•</button>
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="grid grid-cols-2 gap-2.5 p-3 bg-slate-50 rounded-md border border-slate-200">
                    <div>
                        <span class="text-[11px] text-slate-500">Carpet Area</span>
                        <div class="font-bold font-mono text-slate-900 text-xs" x-text="unitModal?.carpet_area + ' sqft'"></div>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-500">Super Builtup Area</span>
                        <div class="font-bold font-mono text-slate-900 text-xs" x-text="unitModal?.super_builtup_area || (unitModal?.carpet_area * 1.35) + ' sqft'"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5 p-3 bg-emerald-50/50 rounded-md border border-emerald-200">
                    <div>
                        <span class="text-[11px] text-emerald-800">Base Unit Rate</span>
                        <div class="font-bold font-mono text-slate-900 text-xs" x-text="'â‚¹' + Number(unitModal?.base_price || 0).toLocaleString()"></div>
                    </div>
                    <div>
                        <span class="text-[11px] text-emerald-800">Final Price</span>
                        <div class="font-bold font-mono text-emerald-700 text-sm" x-text="'â‚¹' + Number(unitModal?.final_price || unitModal?.price || 0).toLocaleString()"></div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-md border border-slate-200">
                    <span class="text-[11px] text-slate-500">Availability Status</span>
                    <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase"
                          :class="unitModal?.status === 'available' ? 'bg-emerald-100 text-emerald-800' : (unitModal?.status === 'hold' ? 'bg-amber-100 text-amber-900' : 'bg-rose-100 text-rose-800')"
                          x-text="unitModal?.status"></span>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                <button type="button" @click="unitModal = null" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md transition cursor-pointer">Close</button>
                @can('manage-projects')
                <button type="button" @click="openEditUnitModal(unitModal); unitModal = null" class="px-4 py-2 bg-[#047857] hover:bg-[#065f46] text-white text-xs font-semibold rounded-md transition cursor-pointer">Edit Unit</button>
                @endcan
            </div>
        </div>
    </div>

    <!-- Add Tower / Building Modal -->
    <div id="addTowerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-md p-5 rounded-lg space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-bold text-slate-900">Add Building Tower</h3>
                <button onclick="document.getElementById('addTowerModal').classList.add('hidden')" class="text-slate-400 font-bold hover:text-slate-600">âœ•</button>
            </div>

            <form method="POST" action="{{ route('projects.store-building', $project->id) }}" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Tower Name *</label>
                        <input type="text" name="name" required placeholder="Tower A / Block 1" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-medium focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Tower Code *</label>
                        <input type="text" name="code" required placeholder="TA" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 uppercase font-bold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Total Floors *</label>
                    <input type="number" name="total_floors" required min="1" value="10" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono font-bold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('addTowerModal').classList.add('hidden')" class="px-3.5 py-1.5 bg-slate-100 text-slate-700 font-semibold rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-[#047857] hover:bg-[#065f46] text-white font-semibold rounded-md shadow-xs">Add Tower</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Unit Modal -->
    <div id="addUnitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-lg p-5 rounded-lg space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-bold text-slate-900">Add Inventory Unit</h3>
                <button onclick="document.getElementById('addUnitModal').classList.add('hidden')" class="text-slate-400 font-bold hover:text-slate-600">âœ•</button>
            </div>

            <form method="POST" action="{{ route('projects.store-unit', $project->id) }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Select Tower / Building *</label>
                    <select name="building_id" required class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-semibold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                        @foreach($project->buildings as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Unit No. *</label>
                        <input type="text" name="unit_number" required placeholder="101" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono font-bold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Floor No.</label>
                        <input type="number" name="floor_number" value="1" min="1" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono font-bold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Unit Type *</label>
                        <select name="unit_type" required class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-semibold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
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
                        <label class="block text-slate-700 mb-1 font-semibold">Carpet Area (sqft) *</label>
                        <input type="number" name="carpet_area" required value="1250" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono font-bold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Base Price (â‚¹) *</label>
                        <input type="number" name="base_price" required value="7500000" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono font-bold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Final Price (â‚¹) *</label>
                        <input type="number" name="final_price" required value="8200000" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono font-bold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('addUnitModal').classList.add('hidden')" class="px-3.5 py-1.5 bg-slate-100 text-slate-700 font-semibold rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-[#047857] hover:bg-[#065f46] text-white font-semibold rounded-md shadow-xs">Save Unit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Unit Modal -->
    <div id="editUnitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-lg p-5 rounded-lg space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-bold text-slate-900">Edit Inventory Unit Specs</h3>
                <button onclick="document.getElementById('editUnitModal').classList.add('hidden')" class="text-slate-400 font-bold hover:text-slate-600">âœ•</button>
            </div>

            <form id="editUnitForm" method="POST" action="" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Unit No. *</label>
                        <input type="text" id="edit_unit_number" name="unit_number" required class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono font-bold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Unit Type *</label>
                        <select id="edit_unit_type" name="unit_type" required class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-semibold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
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
                        <label class="block text-slate-700 mb-1 font-semibold">Carpet Area (sqft) *</label>
                        <input type="number" id="edit_carpet_area" name="carpet_area" required class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono font-bold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Base Price (â‚¹) *</label>
                        <input type="number" id="edit_base_price" name="base_price" required class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono font-bold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Final Price (â‚¹) *</label>
                        <input type="number" id="edit_final_price" name="final_price" required class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono font-bold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('editUnitModal').classList.add('hidden')" class="px-3.5 py-1.5 bg-slate-100 text-slate-700 font-semibold rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-md shadow-xs">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Share Public Link Modal -->
<div id="sharePublicLinkModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
    <div class="bg-white w-full max-w-md p-5 rounded-lg space-y-4 border border-slate-200 shadow-xl">
        <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-link text-emerald-600"></i>
                <h3 class="text-sm font-bold text-slate-900">Share Public Showcase Link</h3>
            </div>
            <button onclick="document.getElementById('sharePublicLinkModal').classList.add('hidden')" class="text-slate-400 font-bold hover:text-slate-600">âœ•</button>
        </div>

        <p class="text-xs text-slate-500 leading-relaxed">
            This public link allows buyers and channel partners to view project overview, unit configurations, and submit lead callbacks.
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

        <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-slate-700">Public Link URL</label>
            <div class="flex items-center space-x-2">
                <input type="text" readonly id="publicProjectUrlInput" value="{{ $publicUrl }}" 
                       class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-xs text-slate-800 font-mono focus:outline-none">
                <button onclick="copyProjectLinkModal()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-md shadow-2xs whitespace-nowrap transition cursor-pointer">
                    <span id="modalCopyBtnText">Copy</span>
                </button>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
            <a href="{{ $publicUrl }}" target="_blank" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-center font-semibold text-xs rounded-md border border-slate-200 transition">
                <i class="fa-solid fa-eye mr-1 text-slate-500"></i>Preview Page
            </a>
            <button onclick="shareWhatsAppModal()" class="flex-1 py-2 bg-[#047857] hover:bg-[#065f46] text-white text-center font-semibold text-xs rounded-md transition shadow-xs cursor-pointer">
                <i class="fa-brands fa-whatsapp mr-1"></i>WhatsApp Share
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

