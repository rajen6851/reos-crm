@extends('layouts.reos')

@section('title', 'CRM Sales Pipeline & Leads – REOS')

@section('content')
<div class="space-y-6" x-data="{ viewMode: 'table' }">
    
    <!-- Breadcrumb & Top Action Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Leads & Sales Pipeline</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="page-heading text-2xl">Leads & Sales Pipeline</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-indigo-50 text-[#4F46E5] border border-indigo-200">
                    {{ $leads->total() }} Total
                </span>
            </div>
            <p class="body-text text-xs mt-0.5">Drag-and-Drop cards between columns or use table view to update lead status instantly.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- View Mode Selector -->
            <div class="bg-slate-100 p-1 rounded-xl border border-[#E2E8F0] flex items-center space-x-1">
                <button type="button" @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-white text-[#0F172A] shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900 font-medium'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                    <i class="fa-solid fa-list mr-1"></i>Table View
                </button>
                <button type="button" @click="viewMode = 'kanban'" :class="viewMode === 'kanban' ? 'bg-white text-[#0F172A] shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900 font-medium'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                    <i class="fa-solid fa-table-columns mr-1"></i>Interactive Board
                </button>
            </div>

            <a href="{{ route('leads.export', request()->all()) }}" class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-[#0F172A] border border-[#E2E8F0] btn-text text-xs rounded-xl transition shadow-2xs">
                <i class="fa-solid fa-download mr-1 text-slate-400"></i>Export CSV
            </a>

            <button onclick="document.getElementById('importCsvModal').classList.remove('hidden')" class="px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-[#059669] border border-emerald-200 btn-text text-xs rounded-xl transition shadow-2xs cursor-pointer">
                <i class="fa-solid fa-upload mr-1"></i>Import CSV
            </button>

            <button onclick="document.getElementById('createLeadModal').classList.remove('hidden')" class="px-5 py-2.5 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text text-xs rounded-xl transition shadow-xs flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-[#DC2626] fa-plus text-xs"></i>
                <span>+ Add Lead</span>
            </button>
        </div>
    </div>

    <!-- CSV Bulk Import Modal -->
    <div id="importCsvModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white max-w-md w-full rounded-3xl p-6 border border-slate-200 shadow-2xl space-y-5">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="font-black text-slate-900 text-lg">Bulk Import Leads (CSV)</h3>
                <button onclick="document.getElementById('importCsvModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <form action="{{ route('leads.import-csv') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Select CSV File</label>
                    <input type="file" name="csv_file" accept=".csv, .txt" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs text-slate-700">
                    <span class="block text-[10px] text-slate-500 mt-1">Columns expected: Name, Phone, Email, Budget</span>
                </div>

                <div class="bg-indigo-50/60 p-3.5 rounded-2xl border border-indigo-100 space-y-2">
                    <label class="flex items-center space-x-2.5 cursor-pointer">
                        <input type="checkbox" name="auto_assign" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                        <span class="font-bold text-indigo-950">Auto Round-Robin Distribution</span>
                    </label>
                    <p class="text-[11px] text-indigo-800 leading-normal">
                        Automatically rotates and assigns imported leads equally across all active company Sales Executives.
                    </p>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" onclick="document.getElementById('importCsvModal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl shadow-xs">Start CSV Import →</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Minimalist Summary Cards Strip -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-medium text-slate-500">All Leads</span>
                <div class="text-xl font-bold text-slate-900 font-mono mt-0.5">{{ $leads->total() }}</div>
            </div>
            <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm"><i class="fa-solid fa-chart-simple text-indigo-600"></i></span>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-medium text-slate-500">Negotiations</span>
                <div class="text-xl font-bold text-amber-600 font-mono mt-0.5">{{ $leads->where('status', 'negotiation')->count() }}</div>
            </div>
            <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm"><i class="fa-solid fa-comments text-amber-600"></i></span>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-medium text-slate-500">Site Visits</span>
                <div class="text-xl font-bold text-purple-600 font-mono mt-0.5">{{ $leads->where('status', 'site_visit')->count() }}</div>
            </div>
            <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm"><i class="fa-solid fa-building text-purple-600"></i></span>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-medium text-slate-500">Converted</span>
                <div class="text-xl font-bold text-emerald-600 font-mono mt-0.5">{{ $leads->whereIn('status', ['converted', 'booked'])->count() }}</div>
            </div>
            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm"><i class="fa-solid fa-trophy text-emerald-600"></i></span>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="p-3 bg-white rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
        <form method="GET" action="{{ route('leads.index') }}" class="flex flex-col md:flex-row items-center gap-2.5 w-full md:w-auto flex-1">
            <div class="relative w-full md:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone or code..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-medium focus:outline-none focus:border-indigo-500">
                <option value="">All Statuses</option>
                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New Leads</option>
                <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                <option value="follow_up" {{ request('status') == 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                <option value="site_visit" {{ request('status') == 'site_visit' ? 'selected' : '' }}>Site Visit</option>
                <option value="interested" {{ request('status') == 'interested' ? 'selected' : '' }}>Interested</option>
                <option value="negotiation" {{ request('status') == 'negotiation' ? 'selected' : '' }}>Negotiation</option>
                <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted (Booked)</option>
                <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
            </select>
            <button type="submit" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl border border-slate-200 transition">Filter</button>
        </form>
    </div>

    <!-- VIEW 1: DRAG & DROP INTERACTIVE PIPELINE KANBAN BOARD -->
    <div x-show="viewMode === 'kanban'" x-transition class="overflow-x-auto pb-6">
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

        <div class="flex space-x-3.5 min-w-[1150px]">
            @foreach($statuses as $stKey => $stMeta)
            @php
                $colLeads = $leads->where('status', $stKey);
            @endphp
            <div class="w-72 shrink-0 bg-slate-50/80 p-3 rounded-2xl border border-slate-200/70 space-y-2.5 kanban-column"
                 ondragover="allowDrop(event)" 
                 ondrop="drop(event, '{{ $stKey }}')">
                 
                <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-white border border-slate-200/80 shadow-2xs font-bold text-xs text-slate-800">
                    <span>{{ $stMeta['name'] }}</span>
                    <span class="px-2 py-0.5 rounded-md font-mono text-[11px] {{ $stMeta['badge'] }}">
                        {{ $colLeads->count() }}
                    </span>
                </div>

                <div class="space-y-2.5 max-h-[680px] overflow-y-auto pr-0.5">
                    @forelse($colLeads as $lead)
                    <div id="lead-card-{{ $lead->id }}" 
                         draggable="true" 
                         ondragstart="drag(event, {{ $lead->id }})"
                         class="p-3.5 rounded-xl bg-white border border-slate-200/80 shadow-2xs hover:border-indigo-400 hover:shadow-md transition space-y-2 text-xs cursor-grab active:cursor-grabbing">
                        
                        <div class="flex justify-between items-center text-[11px]">
                            <span class="font-mono font-semibold text-slate-500">{{ $lead->lead_code }}</span>
                            @if($lead->is_duplicate)
                                <span class="px-1.5 py-0.5 rounded text-[9px] bg-rose-50 text-rose-700 border border-rose-200 font-bold">DUP</span>
                            @endif
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-900 text-xs">{{ $lead->first_name }} {{ $lead->last_name }}</h4>
                            <div class="text-[11px] font-mono text-slate-500 mt-0.5">{{ $lead->phone }}</div>
                        </div>

                        <div class="text-[11px] text-slate-500 flex justify-between items-center pt-2 border-t border-slate-100">
                            <span class="font-medium text-slate-700"><i class="fa-solid fa-user text-slate-400 mr-1"></i>{{ $lead->assignedTo->name ?? 'Unassigned' }}</span>
                            @if($lead->broker || $lead->brokerLead?->broker)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 font-semibold">Broker</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between pt-1 gap-1.5">
                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-semibold hover:bg-emerald-100 transition flex items-center space-x-1">
                                <i class="fa-brands fa-whatsapp text-emerald-600 mr-1"></i>WhatsApp
                            </a>
                            <button onclick="openCallModal({{ $lead->id }}, '{{ $lead->first_name }} {{ $lead->last_name }}')" class="px-2.5 py-1 bg-slate-50 text-slate-700 text-[10px] font-semibold rounded-lg border border-slate-200 hover:bg-slate-100 transition flex items-center space-x-1">
                                <i class="fa-solid fa-phone text-indigo-600 mr-1"></i>Call
                            </button>
                            @if(auth()->user()->isCompanyAdmin() || auth()->user()->isManager() || auth()->user()->role?->slug === 'founder')
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
                        No leads
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- VIEW 2: SIMPLE & CLEAN TABLE VIEW -->
    <div x-show="viewMode === 'table'" x-transition class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Customer Name</th>
                        <th class="py-3 px-4">Contact Information</th>
                        <th class="py-3 px-4">Assigned To</th>
                        <th class="py-3 px-4">Pipeline Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($leads as $lead)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-600">
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
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs flex items-center justify-center border border-indigo-100 shrink-0">
                                    {{ $in }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900">{{ $lead->first_name }} {{ $lead->last_name }}</span>
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
                            @can('assign-leads')
                            <form method="POST" action="{{ route('leads.assign', $lead->id) }}">
                                @csrf
                                <select name="assigned_to_user_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-slate-900 font-medium text-xs focus:outline-none focus:border-indigo-500">
                                    <option value="">Unassigned</option>
                                    @foreach($salesExecutives as $exec)
                                        <option value="{{ $exec->id }}" {{ $lead->assigned_to_user_id == $exec->id ? 'selected' : '' }}>{{ $exec->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                            @else
                            <span class="font-medium text-slate-700 bg-slate-100 px-2 py-1 rounded-lg">
                                {{ $lead->assignedTo->name ?? 'Unassigned' }}
                            </span>
                            @endcan
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
                                        'follow_up' => ['follow_up', 'site_visit', 'interested', 'negotiation', 'lost'],
                                        'site_visit' => ['site_visit', 'interested', 'negotiation', 'lost'],
                                        'interested' => ['interested', 'negotiation', 'lost'],
                                        'negotiation' => ['negotiation', 'lost'],
                                        default => [$lead->status],
                                    };
                                @endphp
                                <form method="POST" action="{{ route('leads.update-status', $lead->id) }}">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs text-slate-900 font-semibold focus:outline-none focus:border-indigo-500">
                                        @foreach($allowedForUser as $st)
                                            <option value="{{ $st }}" {{ $lead->status == $st ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $st)) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                <a href="{{ route('leads.show', $lead->id) }}" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-[#4F46E5] font-bold rounded-lg border border-indigo-200 transition flex items-center space-x-1">
                                    <i class="fa-solid fa-eye text-[#4F46E5]"></i>
                                    <span>View Details</span>
                                </a>

                                <button onclick="openCallModal({{ $lead->id }}, '{{ $lead->first_name }} {{ $lead->last_name }}')" class="px-2.5 py-1 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold rounded-lg border border-slate-200 transition flex items-center space-x-1">
                                    <i class="fa-solid fa-phone text-indigo-600"></i>
                                    <span>Call Log</span>
                                </button>

                                <button onclick="openHistoryModal({{ json_encode($lead) }})" class="px-2.5 py-1 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold rounded-lg border border-slate-200 transition flex items-center space-x-1">
                                    <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
                                    <span>History</span>
                                </button>

                                @if(auth()->user()->isCompanyAdmin() || auth()->user()->isManager() || auth()->user()->role?->slug === 'founder')
                                <form method="POST" action="{{ route('leads.destroy', $lead->id) }}" onsubmit="return confirm('Delete lead {{ $lead->lead_code }}?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Delete Lead">
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
        <div class="p-3 border-t border-slate-100">
            {{ $leads->links() }}
        </div>
    </div>

    <!-- Create Lead Modal -->
    <div id="createLeadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-lg p-6 rounded-2xl space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Add Customer Lead</h3>
                <button onclick="document.getElementById('createLeadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
            </div>
            <form method="POST" action="{{ route('leads.store') }}" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">First Name *</label>
                        <input type="text" name="first_name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Last Name</label>
                        <input type="text" name="last_name" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Phone Number *</label>
                        <input type="text" name="phone" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Email</label>
                        <input type="email" name="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Project Interest</label>
                        <select name="interested_project_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 focus:outline-none focus:border-indigo-500">
                            <option value="">Select Project</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Lead Source</label>
                        <select name="source_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 focus:outline-none focus:border-indigo-500">
                            <option value="">Select Source</option>
                            @foreach($sources as $src)
                                <option value="{{ $src->id }}">{{ $src->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('createLeadModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs">Save Lead</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Call Outcome Modal -->
    <div id="callLogModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-md p-6 rounded-2xl space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Log Call for <span id="callModalLeadName" class="text-indigo-600"></span></h3>
                <button onclick="document.getElementById('callLogModal').classList.add('hidden')" class="text-slate-400 font-bold">✕</button>
            </div>
            <form id="callLogForm" method="POST" action="" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Call / Visit Outcome *</label>
                    <select name="call_outcome" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 focus:outline-none focus:border-indigo-500 font-semibold">
                        <option value="connected">Connected & Spoke</option>
                        <option value="spoke_interested">Connected - High Interest</option>
                        <option value="site_visit_conducted">Site Visit Conducted</option>
                        <option value="interested_after_visit">Interested After Site Visit</option>
                        <option value="scheduled_site_visit">Site Visit Scheduled</option>
                        <option value="busy_callback">Busy / Callback Requested</option>
                        <option value="no_answer">No Answer / Switched Off</option>
                        <option value="not_connected">Not Connected</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Next Follow-up Date & Time</label>
                    <input type="datetime-local" name="next_followup_at" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 focus:outline-none focus:border-indigo-500 font-mono">
                </div>

                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Remarks & Plan</label>
                    <textarea name="notes" rows="3" placeholder="Enter notes from call..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('callLogModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs">Save & Send Alert</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity History Modal -->
    <div id="historyModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-xl max-h-[80vh] overflow-y-auto p-6 rounded-2xl space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">History: <span id="historyCustomerName" class="text-indigo-600"></span></h3>
                <button onclick="document.getElementById('historyModal').classList.add('hidden')" class="text-slate-400 font-bold">✕</button>
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

    function openCallModal(leadId, leadName) {
        document.getElementById('callLogForm').action = "/leads/" + leadId + "/call";
        document.getElementById('callModalLeadName').innerText = leadName;
        document.getElementById('callLogModal').classList.remove('hidden');
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
                    <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl text-xs space-y-1">
                        <div class="flex justify-between items-center font-bold text-slate-900">
                            <span><i class="fa-solid fa-phone text-indigo-600 mr-1"></i>Call Logged by ${c.user ? c.user.name : 'Executive'}</span>
                            <span class="text-[10px] font-mono text-slate-400">${c.called_at ? c.called_at.substring(0, 16) : ''}</span>
                        </div>
                        <p class="text-slate-600 font-medium">${c.notes || 'No remarks entered.'}</p>
                    </div>
                `;
            });

            activities.forEach(function(a) {
                timeline.innerHTML += `
                    <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl text-xs space-y-1">
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
</script>
@endsection
