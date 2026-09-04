@extends('layouts.reos')

@section('title', 'Customer Management Directory - REOS')

@section('content')
<div class="space-y-8">
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Converted Customers Directory</h1>
            <p class="text-xs text-slate-600 mt-1 font-medium">All converted home buyers, booked units, and contact details</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 bg-emerald-50 border border-emerald-200 px-3.5 py-2 rounded-2xl">
            <span class="text-emerald-900">Total Customers: {{ $customers->count() }}</span>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-800 font-extrabold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-4">Customer Name</th>
                        <th class="p-4">Phone / WhatsApp</th>
                        <th class="p-4">Project & Unit</th>
                        <th class="p-4">Assigned Exec</th>
                        <th class="p-4">Channel Attribution</th>
                        <th class="p-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $c)
                    @php
                        $booking = $bookings[$c->id] ?? null;
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-900 flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-black text-xs">
                                {{ strtoupper(substr($c->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-slate-900 font-bold">{{ $c->name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $c->lead_code }}</div>
                            </div>
                        </td>
                        <td class="p-4 text-xs">
                            <div class="font-mono text-slate-900 font-bold">{{ $c->phone }}</div>
                            @if($c->email)
                            <div class="text-slate-500 font-mono">{{ $c->email }}</div>
                            @endif
                        </td>
                        <td class="p-4 text-xs font-bold text-slate-900">
                            <div><i class="fa-solid fa-building text-indigo-600 mr-1"></i>{{ $c->project->name ?? 'N/A' }}</div>
                            @if($booking && $booking->unit)
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-md border border-indigo-200 text-[10px] font-mono mt-1 inline-block">
                                Unit {{ $booking->unit->unit_number }} (Floor {{ $booking->unit->floor->floor_number ?? $booking->unit->floor_number ?? 1 }})
                            </span>
                            @endif
                        </td>
                        <td class="p-4 text-xs">
                            <span class="px-3 py-1 font-bold rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                <i class="fa-solid fa-user text-slate-500 mr-1"></i>{{ $c->assignedTo->name ?? 'Unassigned' }}
                            </span>
                        </td>
                        <td class="p-4 text-xs">
                            @if($c->broker)
                                <span class="px-3 py-1 bg-amber-50 text-amber-900 border border-amber-200 font-bold rounded-full">
                                    <i class="fa-solid fa-handshake text-amber-600 mr-1"></i>{{ $c->broker->agency_name ?? 'Broker Channel' }}
                                </span>
                            @else
                                <span class="px-3 py-1 bg-slate-100 text-slate-700 border border-slate-200 font-bold rounded-full">
                                    Direct Company Lead
                                </span>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($c->phone)
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $c->phone);
                                if (strlen($cleanPhone) === 10) {
                                    $cleanPhone = '91' . $cleanPhone;
                                }
                            @endphp
                            <a href="https://wa.me/{{ $cleanPhone }}?text=Hello%20{{ urlencode($c->name) }},%20thank%20you%20for%20choosing%20REOS!" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs inline-flex items-center space-x-1 transition">
                                <i class="fa-brands fa-whatsapp text-white mr-1 text-sm"></i><span>WhatsApp</span>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500 font-medium text-xs">No converted customers found yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
