@extends('layouts.reos')

@section('title', 'Staff Directory – UrbanProperty')

@section('content')
<div class="space-y-6 pb-12">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-slate-900 to-slate-700 text-white p-6 rounded-2xl shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
        <div class="relative z-10">
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-300 mb-2">
                <a href="{{ route('hrms.dashboard') }}" class="hover:text-white transition">HRMS</a>
                <span>›</span>
                <span class="text-white font-bold">Staff Directory</span>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight">Company Staff Directory</h1>
            <p class="text-slate-300 text-sm mt-1">All internal employees, their roles, and contact information.</p>
        </div>
        <div class="relative z-10 text-right">
            <div class="text-3xl font-black text-white font-mono">{{ $staffUsers->count() }}</div>
            <div class="text-slate-300 text-xs font-bold uppercase tracking-wider">Total Employees</div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white/80 backdrop-blur border border-slate-200 rounded-2xl shadow-sm p-4">
        <input type="text" id="staffSearch" placeholder="Search by name, email, or role..." 
            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            oninput="filterStaff(this.value)">
    </div>

    {{-- Staff Grid --}}
    <div id="staffGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($staffUsers as $staff)
        @php
            $roleName = $staff->role->name ?? 'Unknown';
            $colors = ['indigo','emerald','blue','violet','rose','amber','teal','cyan'];
            $color = $colors[$loop->index % count($colors)];
            $initials = strtoupper(substr($staff->name, 0, 2));
        @endphp
        <div class="staff-card bg-white/80 backdrop-blur border border-slate-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden group"
             data-name="{{ strtolower($staff->name) }}" data-email="{{ strtolower($staff->email) }}" data-role="{{ strtolower($roleName) }}">
            
            {{-- Card top gradient band --}}
            <div class="h-2 bg-gradient-to-r from-{{ $color }}-500 to-{{ $color }}-700"></div>
            
            <div class="p-5">
                {{-- Avatar + Name --}}
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-{{ $color }}-500 to-{{ $color }}-700 flex items-center justify-center text-white font-extrabold text-lg shadow-sm shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-extrabold text-slate-900 text-sm truncate">{{ $staff->name }}</div>
                        @if(auth()->user()->isSaaSFounder())
                        <div class="text-[10px] text-{{ $color }}-600 font-bold bg-{{ $color }}-50 px-2 py-0.5 rounded-full inline-block mt-0.5">{{ $staff->company->name ?? 'Global' }}</div>
                        @endif
                    </div>
                </div>

                {{-- Role badge --}}
                <div class="mb-3">
                    <span class="px-3 py-1 text-[10px] font-extrabold rounded-full bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">
                        {{ ucwords(str_replace('-', ' ', $roleName)) }}
                    </span>
                </div>

                {{-- Details --}}
                <div class="space-y-1.5 text-[11px]">
                    <div class="flex items-center space-x-2 text-slate-600 font-semibold truncate">
                        <i class="fa-solid fa-envelope text-slate-400 shrink-0 w-3 text-center"></i>
                        <span class="truncate">{{ $staff->email }}</span>
                    </div>
                    @if($staff->phone)
                    <div class="flex items-center space-x-2 text-slate-600 font-semibold">
                        <i class="fa-solid fa-phone text-slate-400 shrink-0 w-3 text-center"></i>
                        <span>{{ $staff->phone }}</span>
                    </div>
                    @endif
                    @if($staff->designation)
                    <div class="flex items-center space-x-2 text-slate-600 font-semibold">
                        <i class="fa-solid fa-briefcase text-slate-400 shrink-0 w-3 text-center"></i>
                        <span>{{ $staff->designation }}</span>
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] text-slate-400 font-semibold font-mono">
                        Joined {{ $staff->created_at->format('M Y') }}
                    </span>
                    <span class="flex items-center space-x-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                        <span>Active</span>
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Empty state --}}
    <div id="noStaffFound" class="hidden flex flex-col items-center justify-center py-16 opacity-50">
        <i class="fa-solid fa-user-slash text-5xl text-slate-300 mb-4"></i>
        <span class="text-slate-500 font-bold">No staff members found.</span>
    </div>

</div>

<script>
function filterStaff(query) {
    query = query.toLowerCase().trim();
    const cards = document.querySelectorAll('.staff-card');
    let visible = 0;
    cards.forEach(card => {
        const name = card.dataset.name || '';
        const email = card.dataset.email || '';
        const role = card.dataset.role || '';
        const match = !query || name.includes(query) || email.includes(query) || role.includes(query);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('noStaffFound').classList.toggle('hidden', visible > 0);
    document.getElementById('staffGrid').classList.toggle('hidden', visible === 0);
}
</script>
@endsection
