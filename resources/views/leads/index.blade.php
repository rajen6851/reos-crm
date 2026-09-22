@extends('layouts.reos')

@section('title', 'CRM Sales Pipeline & Leads â€“ UrbanProperty')

@section('content')

<div class="space-y-6 pb-12" x-data="{ viewMode: 'table' }">
    
    <!-- Top Greeting Header & Page Title Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600">Home</a>
                <span>â€º</span>
                <span class="text-slate-900 font-bold">Leads & Sales Pipeline</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-[#0F172A] tracking-tight flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-base shadow-2xs">
                        <i class="fa-solid fa-users-line"></i>
                    </div>
                    <span>CRM Sales Pipeline & Leads</span>
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $leads->total() }} Total Leads
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Manage, assign, and track customer lead conversions across your sales pipeline.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- View Mode Switcher -->
            <div class="bg-slate-100 p-1 rounded-lg border border-slate-200 flex items-center space-x-1">
                <button type="button" @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-[#0F172A] text-white shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'" class="px-3 py-1.5 rounded-md text-xs transition cursor-pointer flex items-center space-x-1.5">
                    <i class="fa-solid fa-list text-xs"></i>
                    <span>Table View</span>
                </button>
                <button type="button" @click="viewMode = 'kanban'" :class="viewMode === 'kanban' ? 'bg-[#0F172A] text-white shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'" class="px-3 py-1.5 rounded-md text-xs transition cursor-pointer flex items-center space-x-1.5">
                    <i class="fa-solid fa-table-columns text-xs"></i>
                    <span>Kanban Board</span>
                </button>
            </div>

            @if(auth()->user()->isCompanyAdmin() || auth()->user()->isManager() || auth()->user()->isSaaSFounder())
            <a href="{{ route('leads.export', request()->all()) }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 font-bold text-xs rounded-lg transition shadow-2xs flex items-center space-x-1.5">
                <i class="fa-solid fa-download text-slate-400 text-xs"></i>
                <span>Export CSV</span>
            </a>

            <button onclick="document.getElementById('importCsvModal').classList.remove('hidden')" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs rounded-lg transition shadow-2xs cursor-pointer flex items-center space-x-1.5">
                <i class="fa-solid fa-file-import text-emerald-600 text-xs"></i>
                <span>Import CSV</span>
            </button>
            @endif

            <button onclick="document.getElementById('createLeadModal').classList.remove('hidden')" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-lg transition shadow-xs flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add New Lead</span>
            </button>
        </div>
    </div>

    <!-- Bulk CSV Import Modal -->
    <div id="importCsvModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white max-w-md w-full rounded-2xl p-5 border border-slate-200 shadow-2xl space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                <h3 class="font-bold text-slate-900 text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-file-csv text-emerald-600 text-base"></i>
                    <span>Bulk Import Leads (CSV)</span>
                </h3>
                <button onclick="document.getElementById('importCsvModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">âœ•</button>
            </div>

            <form action="{{ route('leads.import-csv') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Select CSV File</label>
                    <input type="file" name="csv_file" accept=".csv, .txt" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs text-slate-700">
                    <span class="block text-[10px] text-slate-500 mt-1">Expected column headers: Name, Phone, Email, Budget</span>
                </div>

                <div class="bg-blue-50/60 p-3.5 rounded-xl border border-blue-200 space-y-1.5">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="auto_assign" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                        <span class="font-bold text-blue-950">Auto Round-Robin Distribution</span>
                    </label>
                    <p class="text-[11px] text-blue-800 leading-normal">
                        Automatically rotates and assigns imported leads equally across all active company Sales Executives.
                    </p>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-200">
                    <button type="button" onclick="document.getElementById('importCsvModal').classList.add('hidden')" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg border border-slate-200">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-xs">Start Import â†’</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DYNAMIC AJAX LEADS CONTAINER -->
    <div id="leadsDynamicContainer" class="space-y-6 transition-opacity duration-200">
        
        <!-- Summary KPI Cards Strip -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500">Total Pipeline</span>
                    <div class="text-2xl font-bold text-slate-900 font-mono mt-0.5">{{ $leads->total() }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center text-base font-bold shadow-2xs">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500">Active Negotiations</span>
                    <div class="text-2xl font-bold text-amber-600 font-mono mt-0.5">{{ $leads->where('status', 'negotiation')->count() }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-base font-bold shadow-2xs">
                    <i class="fa-solid fa-comments"></i>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500">Site Visits Scheduled</span>
                    <div class="text-2xl font-bold text-purple-600 font-mono mt-0.5">{{ $leads->where('status', 'site_visit')->count() }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center text-base font-bold shadow-2xs">
                    <i class="fa-solid fa-building-user"></i>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500">Converted Bookings</span>
                    <div class="text-2xl font-bold text-emerald-600 font-mono mt-0.5">{{ $leads->whereIn('status', ['converted', 'booked'])->count() }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-base font-bold shadow-2xs">
                    <i class="fa-solid fa-trophy"></i>
                </div>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs space-y-3.5 text-xs">
            <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                <!-- Search Keyword & Employee Filter Form -->
                <form method="GET" action="{{ route('leads.index') }}" onsubmit="filterLeadsAjaxForm(this, event)" class="flex flex-wrap items-center gap-2.5 w-full md:w-auto flex-1">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif

                    <div class="relative w-full md:w-72">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone or code..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-slate-900 focus:outline-none focus:border-blue-500 transition text-xs font-medium">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    </div>

                    @if(!auth()->user()->isSales())
                    <div class="relative w-full md:w-64">
                        <select name="assigned_to_user_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-blue-500 font-semibold text-xs transition cursor-pointer">
                            <option value="">ðŸ‘¤ Filter by Employee / Staff</option>
                            <option value="unassigned" {{ request('assigned_to_user_id') === 'unassigned' ? 'selected' : '' }}>âš ï¸ Unassigned Leads</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('assigned_to_user_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->role?->name ?? 'Staff' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <button type="submit" class="px-4 py-2 bg-[#0F172A] hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        <span>Search</span>
                    </button>

                    @if(request('status') || request('search') || request('assigned_to_user_id'))
                        <a href="{{ route('leads.index') }}" onclick="filterLeadsAjax('{{ route('leads.index') }}', event)" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition cursor-pointer flex items-center space-x-1">
                            <i class="fa-solid fa-xmark text-slate-400"></i>
                            <span>Clear Filters</span>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Clickable Status Filter Buttons Bar -->
            @php
                $statusOptions = [
                    '' => 'All Statuses',
                    'new' => 'New Leads',
                    'contacted' => 'Contacted',
                    'follow_up' => 'Follow Up',
                    'site_visit' => 'Site Visit',
                    'interested' => 'Interested',
                    'negotiation' => 'Negotiation',
                    'converted' => 'Converted',
                    'lost' => 'Lost',
                ];
                $currentStatus = request('status', '');
            @endphp
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 pt-2.5 border-t border-slate-100">
                <span class="text-[11px] font-bold uppercase text-slate-400 tracking-wider shrink-0 mr-1 flex items-center space-x-1">
                    <i class="fa-solid fa-filter text-blue-600 text-xs"></i>
                    <span>Pipeline Status:</span>
                </span>
                @foreach($statusOptions as $key => $label)
                    @php
                        $isActive = ($currentStatus === (string)$key) || ($key === '' && empty($currentStatus));
                        $queryParams = array_filter(array_merge(request()->except('page'), [
                            'status' => $key !== '' ? $key : null,
                            'assigned_to_user_id' => request('assigned_to_user_id'),
                        ]));
                        $ajaxUrl = route('leads.index', $queryParams);
                    @endphp
                    <a href="{{ $ajaxUrl }}" 
                       onclick="filterLeadsAjax('{{ $ajaxUrl }}', event)"
                       class="px-3.5 py-1.5 rounded-lg font-bold text-xs transition border cursor-pointer whitespace-nowrap flex items-center space-x-1.5 {{ $isActive ? 'bg-[#0F172A] text-white border-[#0F172A] shadow-2xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                        <span>{{ $label }}</span>
                        @if($isActive)
                            <i class="fa-solid fa-check text-[10px]"></i>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

    <!-- VIEW 1: INTERACTIVE KANBAN BOARD -->
    <div x-show="viewMode === 'kanban'" x-transition class="overflow-x-auto pb-6" x-cloak>
        @php
            $statuses = [
                'new' => ['name' => 'New Leads', 'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
                'contacted' => ['name' => 'Contacted', 'badge' => 'bg-sky-50 text-sky-700 border-sky-200'],
                'follow_up' => ['name' => 'Follow Up', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200'],
                'site_visit' => ['name' => 'Site Visit', 'badge' => 'bg-purple-50 text-purple-700 border-purple-200'],
                'negotiation' => ['name' => 'Negotiation', 'badge' => 'bg-rose-50 text-rose-700 border-rose-200'],
                'converted' => ['name' => 'Converted', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            ];
        @endphp

        <div class="flex space-x-4 min-w-[1150px]">
            @foreach($statuses as $stKey => $stMeta)
            @php
                $colLeads = $leads->where('status', $stKey);
            @endphp
            <div class="w-72 shrink-0 bg-slate-50/70 p-3 rounded-xl border border-slate-200 space-y-3 kanban-column"
                 ondragover="allowDrop(event)" 
                 ondrop="drop(event, '{{ $stKey }}')">
                 
                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-white border border-slate-200 shadow-2xs font-bold text-xs text-slate-800">
                    <span class="flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span>{{ $stMeta['name'] }}</span>
                    </span>
                    <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold border {{ $stMeta['badge'] }}">
                        {{ $colLeads->count() }}
                    </span>
                </div>

                <div class="space-y-2.5 max-h-[680px] overflow-y-auto pr-0.5">
                    @forelse($colLeads as $lead)
                    <div id="lead-card-{{ $lead->id }}" 
                         draggable="true" 
                         ondragstart="drag(event, {{ $lead->id }})"
                         class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-2xs hover:border-blue-300 hover:shadow-md transition space-y-2.5 text-xs cursor-grab active:cursor-grabbing">
                        
                        <div class="flex justify-between items-center text-[11px]">
                            <span class="font-mono font-bold text-slate-500">{{ $lead->lead_code }}</span>
                            @if($lead->is_duplicate)
                                <span class="px-1.5 py-0.5 rounded text-[9px] bg-rose-50 text-rose-700 border border-rose-200 font-bold">DUP</span>
                            @endif
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-900 text-xs">{{ $lead->first_name }} {{ $lead->last_name }}</h4>
                            <div class="text-[11px] font-mono text-slate-500 mt-0.5">{{ $lead->phone }}</div>
                        </div>

                        <div class="text-[11px] text-slate-500 flex flex-col gap-1 pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-purple-800 text-[10px] bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200 inline-flex items-center space-x-1" title="Assigned Manager">
                                    <i class="fa-solid fa-user-tie text-purple-600 text-[10px]"></i>
                                    <span>Mgr: {{ $lead->assignedManager->name ?? 'None' }}</span>
                                </span>
                                @if($lead->broker || $lead->brokerLead?->broker)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 font-semibold">Broker</span>
                                @endif
                            </div>
                            <div class="font-medium text-slate-700 text-[11px]">
                                <i class="fa-solid fa-user text-slate-400 mr-1"></i>Exec: {{ $lead->assignedTo->name ?? 'Unassigned' }}
                            </div>
                            @if($lead->latestDistributionLog && $lead->latestDistributionLog->rule)
                            <div class="font-medium text-slate-500 text-[9px] mt-0.5" title="Method: {{ ucfirst(str_replace('_', ' ', $lead->latestDistributionLog->distribution_method)) }}">
                                <i class="fa-solid fa-robot text-slate-400 mr-1"></i>Rule: {{ Str::limit($lead->latestDistributionLog->rule->name, 20) }}
                            </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between pt-1 gap-1.5">
                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold hover:bg-emerald-100 transition flex items-center space-x-1">
                                <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                <span>WhatsApp</span>
                            </a>
                            <button onclick="openCallModal({{ json_encode($lead) }}, '{{ addslashes($lead->first_name . ' ' . $lead->last_name) }}')" class="px-2.5 py-1 bg-slate-50 text-slate-700 text-[10px] font-bold rounded-lg border border-slate-200 hover:bg-slate-100 transition flex items-center space-x-1">
                                <i class="fa-solid fa-phone text-blue-600"></i>
                                <span>Call Log</span>
                            </button>
                            <!-- Quick Actions Dropdown (Mobile) -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" class="px-2.5 py-1 bg-slate-50 hover:bg-slate-100 text-slate-700 text-[10px] font-bold rounded-lg border border-slate-200 transition flex items-center space-x-1">
                                    <i class="fa-solid fa-bolt text-amber-500"></i>
                                    <span>Action</span>
                                </button>
                                
                                <div x-show="open" x-cloak class="absolute bottom-full right-0 mb-1 w-44 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden z-50 text-left py-1">
                                    <a href="{{ route('leads.site-visit.create', $lead->id) }}" class="block w-full text-left px-3 py-2 text-[10px] font-bold text-slate-700 hover:bg-indigo-50">
                                        <i class="fa-solid fa-map-location-dot w-4 text-indigo-500"></i> Site Visit
                                    </a>
                                    <a href="{{ route('leads.negotiate.create', $lead->id) }}" class="block w-full text-left px-3 py-2 text-[10px] font-bold text-slate-700 hover:bg-amber-50">
                                        <i class="fa-solid fa-handshake-angle w-4 text-amber-500"></i> Negotiate
                                    </a>
                                    <a href="{{ route('leads.convert.create', $lead->id) }}" class="block w-full text-left px-3 py-2 text-[10px] font-bold text-slate-700 hover:bg-emerald-50">
                                        <i class="fa-solid fa-money-check-dollar w-4 text-emerald-500"></i> Booking
                                    </a>
                                    <div class="h-px bg-slate-100 my-1"></div>
                                    <a href="{{ route('leads.lost.create', $lead->id) }}" class="block w-full text-left px-3 py-2 text-[10px] font-bold text-rose-600 hover:bg-rose-50">
                                        <i class="fa-solid fa-thumbs-down w-4"></i> Drop
                                    </a>
                                </div>
                            </div>
                            @if(auth()->user()->isCompanyAdmin() || auth()->user()->isSaaSFounder())
                            <form method="POST" action="{{ route('leads.destroy', $lead->id) }}" onsubmit="return confirm('Delete lead {{ $lead->lead_code }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition" title="Delete Lead">
                                    <i class="fa-solid fa-trash-can text-rose-500"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-xs text-slate-400 font-medium bg-white/50 rounded-xl border border-dashed border-slate-200">
                        No leads in this stage
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- VIEW 2: TABLE VIEW -->
    <div x-show="viewMode === 'table'" x-transition class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4">Code</th>
                        <th class="py-3.5 px-4">Customer Name</th>
                        <th class="py-3.5 px-4">Contact Information</th>
                        <th class="py-3.5 px-4">Assigned Manager</th>
                        <th class="py-3.5 px-4">Assigned Exec</th>
                        <th class="py-3.5 px-4">Pipeline Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($leads as $lead)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-600">
                            <span>{{ $lead->lead_code }}</span>
                            @if($lead->is_duplicate)
                                <span class="ml-1 px-1.5 py-0.5 text-[9px] bg-rose-50 text-rose-700 border border-rose-200 rounded font-bold">DUP</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-2.5">
                                @php
                                    $np = explode(' ', trim($lead->first_name . ' ' . $lead->last_name));
                                    $in = count($np) >= 2 ? strtoupper(substr($np[0], 0, 1) . substr($np[count($np) - 1], 0, 1)) : strtoupper(substr($lead->first_name, 0, 2));
                                @endphp
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold text-xs flex items-center justify-center border border-blue-200 shrink-0 shadow-2xs">
                                    {{ $in }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 text-xs">{{ $lead->first_name }} {{ $lead->last_name }}</span>
                                    @if($lead->broker || $lead->brokerLead?->broker)
                                        @php
                                            $bObj = $lead->broker ?? $lead->brokerLead->broker;
                                        @endphp
                                        <div class="text-[10px] text-amber-800 font-medium"><i class="fa-solid fa-handshake text-amber-600 mr-1"></i>{{ $bObj->agency_name ?? $bObj->user->name ?? 'Broker' }}</div>
                                    @elseif($lead->source)
                                        <div class="text-[10px] text-slate-400 font-medium">{{ $lead->source->name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono">
                            <div class="flex items-center space-x-1.5">
                                <span class="font-semibold text-slate-900">{{ $lead->phone }}</span>
                                <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold hover:bg-emerald-100 transition">
                                    WA
                                </a>
                            </div>
                            <div class="text-[11px] text-slate-400 font-normal">{{ $lead->email }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($lead->assignedManager)
                                <span class="inline-flex items-center space-x-1 font-bold text-purple-800 bg-purple-50 border border-purple-200 px-2.5 py-1 rounded-lg text-xs">
                                    <i class="fa-solid fa-user-tie text-purple-600 text-[11px]"></i>
                                    <span>{{ $lead->assignedManager->name }}</span>
                                </span>
                            @else
                                <span class="text-slate-400 font-medium italic text-xs bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200">Not Assigned</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @can('assign-leads')
                            <form method="POST" action="{{ route('leads.assign', $lead->id) }}">
                                @csrf
                                <select name="assigned_to_user_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-slate-900 font-medium text-xs focus:outline-none focus:border-blue-500 cursor-pointer">
                                    <option value="">Unassigned</option>
                                    @foreach($salesExecutives as $exec)
                                        <option value="{{ $exec->id }}" {{ $lead->assigned_to_user_id == $exec->id ? 'selected' : '' }}>{{ $exec->name }}</option>
                                    @endforeach
                                    @if($lead->assigned_to_user_id && !$salesExecutives->contains('id', $lead->assigned_to_user_id))
                                        <option value="{{ $lead->assigned_to_user_id }}" selected>{{ $lead->assignedTo->name ?? 'Unknown User' }} (Other)</option>
                                    @endif
                                </select>
                            </form>
                            @else
                            <span class="font-medium text-slate-700 bg-slate-100 px-2 py-1 rounded-lg">
                                {{ $lead->assignedTo->name ?? 'Unassigned' }}
                            </span>
                            @endcan
                            @if($lead->latestDistributionLog && $lead->latestDistributionLog->rule)
                            <div class="mt-1.5 text-[10px] text-slate-500 flex items-center space-x-1" title="Method: {{ ucfirst(str_replace('_', ' ', $lead->latestDistributionLog->distribution_method)) }}">
                                <i class="fa-solid fa-robot text-slate-400"></i>
                                <span class="truncate max-w-[120px]">{{ $lead->latestDistributionLog->rule->name }}</span>
                            </div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($lead->status === 'converted')
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    <span>Converted</span>
                                </span>
                            @elseif($lead->status === 'lost' && auth()->user()->isSales())
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                                    Lost
                                </span>
                            @else
                                @php
                                    $allowedForUser = match($lead->status) {
                                        'new' => ['new', 'contacted', 'lost'],
                                        'contacted' => ['contacted', 'follow_up', 'site_visit', 'interested', 'lost'],
                                        'follow_up' => ['follow_up', 'site_visit', 'interested', 'lost'],
                                        'site_visit' => ['site_visit', 'interested', 'lost'],
                                        'interested' => ['interested', 'lost'],
                                        'negotiation' => ['negotiation', 'lost'],
                                        default => [$lead->status],
                                    };
                                @endphp
                                <form method="POST" action="{{ route('leads.update-status', $lead->id) }}">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs text-slate-900 font-semibold focus:outline-none focus:border-blue-500 cursor-pointer">
                                        @foreach($allowedForUser as $st)
                                            <option value="{{ $st }}" {{ $lead->status == $st ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $st)) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                <a href="{{ route('leads.show', $lead->id) }}" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-lg border border-blue-200 transition flex items-center space-x-1">
                                    <i class="fa-solid fa-eye text-blue-600"></i>
                                    <span>Details</span>
                                </a>

                                <button onclick="openCallModal({{ json_encode($lead) }}, '{{ addslashes($lead->first_name . ' ' . $lead->last_name) }}')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg border border-slate-200 transition flex items-center space-x-1 cursor-pointer">
                                    <i class="fa-solid fa-phone text-blue-600"></i>
                                    <span>Call Log</span>
                                </button>
                                
                                <!-- Quick Actions Dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" @click.away="open = false" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg border border-slate-200 transition flex items-center space-x-1 cursor-pointer">
                                        <i class="fa-solid fa-bolt text-amber-500"></i>
                                        <span>Action</span>
                                        <i class="fa-solid fa-chevron-down text-[10px] ml-1"></i>
                                    </button>
                                    
                                    <div x-show="open" x-cloak class="absolute right-0 mt-1 w-48 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden z-20 text-left py-1">
                                        <a href="{{ route('leads.site-visit.create', $lead->id) }}" class="block w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 flex items-center">
                                            <i class="fa-solid fa-map-location-dot w-5 text-indigo-500"></i> Schedule Site Visit
                                        </a>
                                        @if(!auth()->user()->isSales())
                                        <a href="{{ route('leads.negotiate.create', $lead->id) }}" class="block w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-amber-50 hover:text-amber-700 flex items-center">
                                            <i class="fa-solid fa-handshake-angle w-5 text-amber-500"></i> Start Negotiation
                                        </a>
                                        <a href="{{ route('leads.convert.create', $lead->id) }}" class="block w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 flex items-center">
                                            <i class="fa-solid fa-money-check-dollar w-5 text-emerald-500"></i> Record Booking
                                        </a>
                                        @endif
                                        <div class="h-px bg-slate-100 my-1"></div>
                                        <a href="{{ route('leads.lost.create', $lead->id) }}" class="block w-full text-left px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 flex items-center">
                                            <i class="fa-solid fa-thumbs-down w-5"></i> Drop Lead
                                        </a>
                                    </div>
                                </div>

                                <button onclick="openHistoryModal({{ json_encode($lead) }})" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg border border-slate-200 transition flex items-center space-x-1 cursor-pointer">
                                    <i class="fa-solid fa-clock-rotate-left text-slate-500"></i>
                                </button>

                                @if(auth()->user()->isCompanyAdmin() || auth()->user()->isSaaSFounder())
                                <form method="POST" action="{{ route('leads.destroy', $lead->id) }}" onsubmit="return confirm('Delete lead {{ $lead->lead_code }}?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition cursor-pointer" title="Delete Lead">
                                        <i class="fa-solid fa-trash-can text-rose-500"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $leads->links() }}
        </div>
    </div>
    </div> <!-- End #leadsDynamicContainer -->

    <!-- Create Lead Modal -->
    <div id="createLeadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white w-full max-w-lg overflow-hidden shadow-2xl border border-slate-300">
            <!-- Header -->
            <div class="flex justify-between items-start px-5 py-4 bg-[#4A86BA] text-white relative">
                <div>
                    <h3 class="text-xl font-bold mb-1">Add New Customer Lead</h3>
                    <div class="text-[13px] font-semibold text-blue-100">Enter primary details to create a lead</div>
                </div>
                <button onclick="document.getElementById('createLeadModal').classList.add('hidden')" class="text-white hover:text-slate-200 text-lg absolute right-4 top-3">âœ•</button>
            </div>

            <form method="POST" action="{{ route('leads.store') }}" class="p-5 text-sm space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">First Name <span class="text-orange-500">*</span></label>
                        <input type="text" name="first_name" required class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                    </div>
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Last Name</label>
                        <input type="text" name="last_name" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Phone Number <span class="text-orange-500">*</span></label>
                        <input type="text" name="phone" required class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                    </div>
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Email</label>
                        <input type="email" name="email" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Project Interest</label>
                        <select name="interested_project_id" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                            <option value="">Select Project</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Lead Source</label>
                        <select name="source_id" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                            <option value="">Select Source</option>
                            @foreach($sources as $src)
                                <option value="{{ $src->id }}">{{ $src->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="document.getElementById('createLeadModal').classList.add('hidden')" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded shadow-sm text-[13px] border border-slate-300">Cancel</button>
                    <button type="submit" class="bg-[#4A86BA] hover:bg-[#386b99] text-white font-bold py-1.5 px-6 rounded shadow text-[13px]">Save Lead</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Follow-up Modal -->
    <div id="callLogModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white w-full max-w-3xl overflow-hidden shadow-2xl border border-slate-300">
            <!-- Header (Blue like the screenshot) -->
            <div class="flex justify-between items-start px-5 py-4 bg-[#4A86BA] text-white relative">
                <div>
                    <h3 class="text-xl font-bold mb-2">Follow-up</h3>
                    <div class="flex items-center space-x-6 text-[13px] font-semibold">
                        <div>Customer Name: <span id="callModalLeadName"></span></div>
                        <div>Phone No. <span id="callModalLeadPhone"></span></div>
                    </div>
                </div>
                <button onclick="document.getElementById('callLogModal').classList.add('hidden')" class="text-white hover:text-slate-200 text-lg absolute right-4 top-3">âœ•</button>
            </div>

            <form id="callLogForm" method="POST" action="" class="p-5 text-sm" x-data="{ pipelineStage: 'IN FOLLOWUP' }">
                @csrf
                <input type="hidden" name="call_outcome" value="connected">

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <!-- Inquiry Status -->
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Inquiry Status <span class="text-orange-500">*</span></label>
                        <select name="status" x-model="pipelineStage" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                            <option value="Meeting at Client Place">Meeting at Client Place</option>
                            <option value="Not Interested">Not Interested</option>
                            <option value="WARM">WARM</option>
                            <option value="IN FOLLOWUP">IN FOLLOWUP</option>
                            <option value="READY TO VISIT">READY TO VISIT</option>
                            <option value="NOT CONNECTED">NOT CONNECTED</option>
                        </select>
                    </div>
                    <!-- Next Followup Date -->
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Next Followup Date <span class="text-orange-500">*</span></label>
                        <input type="datetime-local" name="next_followup_at" required class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                    </div>
                    <!-- Budget Upto -->
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Budget Upto</label>
                        <select name="budget_max" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                            <option value="">Select Budget Upto</option>
                            <option value="1500000">15 Lacs</option>
                            <option value="3000000">30 Lacs</option>
                            <option value="5000000">50 Lacs</option>
                            <option value="10000000">1 Crore</option>
                        </select>
                    </div>
                </div>

                <!-- Remarks & Save Button -->
                <div class="flex items-end space-x-3 mb-6">
                    <div class="grow">
                        <textarea name="notes" rows="3" class="w-full bg-white border border-slate-300 p-2 focus:outline-none focus:border-blue-500 text-[13px] resize-none"></textarea>
                    </div>
                    <button type="submit" class="bg-[#4A86BA] hover:bg-[#386b99] text-white font-bold py-1.5 px-6 rounded shadow shrink-0 mb-4">
                        Save
                    </button>
                </div>

                <!-- Recent Follow Up Detail Table -->
                <div class="bg-[#DCEBF6] font-bold text-center text-[#1F2937] py-1 text-[14px]">
                    Recent Follow Up Detail
                </div>
                <div class="max-h-40 overflow-y-auto bg-slate-100 p-0 border border-t-0 border-slate-200">
                    <table class="w-full text-left text-[12px] text-slate-700">
                        <thead class="bg-slate-200 text-slate-800 font-bold sticky top-0 border-b border-slate-300">
                            <tr>
                                <th class="py-2 px-3 text-center w-10">S/No.</th>
                                <th class="py-2 px-3">Type</th>
                                <th class="py-2 px-3">Followup Date</th>
                                <th class="py-2 px-3">Remark</th>
                                <th class="py-2 px-3">Next Schedule date</th>
                                <th class="py-2 px-3">Status</th>
                                <th class="py-2 px-3">CreatedBy</th>
                            </tr>
                        </thead>
                        <tbody id="modalHistoryLogsContainer" class="divide-y divide-slate-300 bg-white">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
                <div class="text-[11px] text-red-500 font-semibold mt-4">
                    *ccoa: customer click on ad, *ccoaa: customer click on ad again (open lead &Rightarrow; dump lead)
                </div>
            </form>
        </div>
    </div>

    <!-- Activity History Modal -->
    <div id="historyModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white w-full max-w-xl max-h-[80vh] overflow-y-auto p-6 rounded-3xl space-y-4 border border-slate-100 shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-[#0F172A]">Activity History: <span id="historyCustomerName" class="text-blue-600"></span></h3>
                <button onclick="document.getElementById('historyModal').classList.add('hidden')" class="text-slate-400 font-bold">âœ•</button>
            </div>
            <div id="historyTimelineContent" class="space-y-2.5">
                <!-- Timeline entries injected via JS -->
            </div>
        </div>
    </div>
</div>

<script>
    function drag(ev, leadId) {
        ev.dataTransfer.setData("text/plain", leadId);
    }

    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drop(ev, newStatus) {
        ev.preventDefault();
        var leadId = ev.dataTransfer.getData("text/plain");
        if (!leadId || !newStatus) return;

        if (newStatus === 'converted') {
            alert('Leads cannot be manually moved to Converted from Kanban. Please create a Unit Booking to convert this lead.');
            return;
        }

        // Submit status update via Form submission
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/leads/' + leadId + '/status';
        
        var csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfInput);

        var statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = newStatus;
        form.appendChild(statusInput);

        document.body.appendChild(form);
        form.submit();
    }

    function openCallModal(lead, leadName) {
        document.getElementById('callLogForm').action = "/leads/" + lead.id + "/call";
        document.getElementById('callModalLeadName').textContent = leadName || (lead.first_name + ' ' + (lead.last_name || ''));
        document.getElementById('callModalLeadPhone').textContent = lead.phone || 'N/A';
        
        const container = document.getElementById('modalHistoryLogsContainer');
        if (container) {
            container.innerHTML = '';
            const calls = lead.calls || [];
            if(calls.length === 0) {
                container.innerHTML = '<tr><td colspan="7" class="py-4 text-center text-slate-400 italic">No previous call logs recorded.</td></tr>';
            } else {
                calls.forEach((c, index) => {
                    const dateFormatted = c.created_at ? new Date(c.created_at).toLocaleString() : '';
                    const nextSchedule = c.next_followup_at ? new Date(c.next_followup_at).toLocaleString() : '-';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="py-2 px-3 text-center">${index + 1}</td>
                        <td class="py-2 px-3 text-slate-800">Lead</td>
                        <td class="py-2 px-3">${dateFormatted}</td>
                        <td class="py-2 px-3 font-semibold text-slate-900">${c.notes || c.description || ''}</td>
                        <td class="py-2 px-3 text-slate-800">${nextSchedule}</td>
                        <td class="py-2 px-3 text-slate-800 font-semibold uppercase">${c.lead_status_snapshot || (c.activity_type ? c.activity_type.replace(/_/g, ' ') : lead.status)}</td>
                        <td class="py-2 px-3 text-slate-800 font-bold uppercase">${c.user ? c.user.name : 'SYSTEM'}</td>
                    `;
                    container.appendChild(tr);
                });
            }
        }
        
        document.getElementById('callLogModal').classList.remove('hidden');
    }

    function handleAudioSelect(input) {
        if (input.files && input.files[0]) {
            showAudioPreview(input.files[0]);
        }
    }

    function openHistoryModal(lead) {
        document.getElementById('historyCustomerName').innerText = (lead.first_name || '') + ' ' + (lead.last_name || '') + ' (' + lead.lead_code + ')';
        var timeline = document.getElementById('historyTimelineContent');
        timeline.innerHTML = '';

        var activities = lead.activities || [];
        var calls = lead.calls || [];

        if (activities.length === 0 && calls.length === 0) {
            timeline.innerHTML = '<div class="text-slate-400 text-xs text-center py-4 font-medium">No activity recorded yet for this lead.</div>';
        } else {
            calls.forEach(function(c) {
                timeline.innerHTML += `
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1">
                        <div class="flex justify-between items-center font-bold text-slate-900">
                            <span><i class="fa-solid fa-phone text-blue-600 mr-1"></i>Call Logged by ${c.user ? c.user.name : 'Executive'}</span>
                            <span class="text-[10px] font-mono text-slate-400">${c.called_at ? c.called_at.substring(0, 16) : ''}</span>
                        </div>
                        <p class="text-slate-600 font-medium">${c.notes || 'No remarks entered.'}</p>
                    </div>
                `;
            });

            activities.forEach(function(a) {
                timeline.innerHTML += `
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1">
                        <div class="flex justify-between items-center font-bold text-slate-800">
                            <span><i class="fa-solid fa-bolt text-amber-500 mr-1"></i>${a.activity_type ? a.activity_type.replace('_', ' ').toUpperCase() : 'ACTIVITY'}</span>
                            <span class="text-[10px] font-mono text-slate-400">${a.created_at ? a.created_at.substring(0, 16) : ''}</span>
                        </div>
                        <p class="text-slate-600">${a.description || ''}</p>
                    </div>
                `;
            });
        }

        document.getElementById('historyModal').classList.remove('hidden');
    }

    // Dynamic AJAX Filter Engine
    window.filterLeadsAjax = function(url, event) {
        if (event) {
            event.preventDefault();
        }

        const container = document.getElementById('leadsDynamicContainer');
        if (!container) {
            window.location.href = url;
            return;
        }

        container.style.opacity = '0.4';
        container.style.pointerEvents = 'none';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContainer = doc.getElementById('leadsDynamicContainer');

            if (newContainer) {
                container.innerHTML = newContainer.innerHTML;
                window.history.pushState({}, '', url);
            } else {
                window.location.href = url;
            }
        })
        .catch(err => {
            console.error('AJAX Filter Error:', err);
            window.location.href = url;
        })
        .finally(() => {
            if (container) {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            }
        });
    };

    window.filterLeadsAjaxForm = function(form, event) {
        if (event) {
            event.preventDefault();
        }
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const url = form.action + '?' + params.toString();
        filterLeadsAjax(url, event);
    };
</script>
@endsection
