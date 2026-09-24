@extends('layouts.reos')

@section('title', 'Start Negotiation – ' . $lead->first_name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center space-x-3 text-sm text-slate-500 mb-4">
        <a href="{{ route('leads.index') }}" class="hover:text-blue-600">Leads</a>
        <span>›</span>
        <a href="{{ route('leads.show', $lead->id) }}" class="hover:text-blue-600 font-semibold text-slate-800">{{ $lead->first_name }} {{ $lead->last_name }}</a>
        <span>›</span>
        <span class="text-amber-600 font-bold">Start Negotiation</span>
    </div>

    <div class="bg-white w-full max-w-3xl overflow-hidden shadow-sm border border-slate-300">
        <div class="flex justify-between items-start px-5 py-4 bg-[#4A86BA] text-white relative">
            <div>
                <h3 class="text-xl font-bold mb-2">Start Negotiation</h3>
                <div class="flex items-center space-x-6 text-[13px] font-semibold">
                    <div>Customer Name: <span class="text-blue-100">{{ $lead->first_name }} {{ $lead->last_name }}</span></div>
                    <div>Phone No. <span class="text-blue-100">{{ $lead->phone }}</span></div>
                </div>
            </div>
        </div>

        <form action="{{ route('leads.negotiate', $lead->id) }}" method="POST" class="p-5 text-sm space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Offered Price (₹) <span class="text-orange-500">*</span></label>
                    <input type="number" name="neg_offered_price" required placeholder="e.g. 4500000" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                </div>

                <div>
                    <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Expected Closing Date <span class="text-orange-500">*</span></label>
                    <input type="date" name="neg_expected_close_date" required class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                </div>
            </div>

            <div>
                <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Client Demands / Negotiation Notes</label>
                <textarea name="neg_remarks" rows="3" placeholder="Client is asking for complimentary club membership..." class="w-full bg-white border border-slate-300 p-2 focus:outline-none focus:border-blue-500 text-[13px] resize-none"></textarea>
            </div>


            <!-- Recent Interaction History -->
            @php
                $recentHistory = collect()
                    ->merge($lead->calls()->latest()->take(5)->get())
                    ->merge($lead->activities()->latest()->take(5)->get())
                    ->sortByDesc('created_at')
                    ->take(5);
            @endphp
            @if($recentHistory->isNotEmpty())
            <div class="mt-8 border border-slate-200 overflow-hidden">
                <div class="bg-[#DCEBF6] font-bold text-center text-[#1F2937] py-1 text-[14px]">
                    Recent Follow Up Detail
                </div>
                <div class="max-h-40 overflow-y-auto bg-slate-100 p-0 border-t-0 border-slate-200">
                    <table class="w-full text-left text-[12px] text-slate-700">
                        <thead class="bg-slate-200 text-slate-800 font-bold sticky top-0 border-b border-slate-300">
                            <tr>
                                <th class="py-2 px-3 text-center w-10">S/No.</th>
                                <th class="py-2 px-3">Date</th>
                                <th class="py-2 px-3">Type</th>
                                <th class="py-2 px-3">Remark</th>
                                <th class="py-2 px-3">Logged By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-300 bg-white">
                            @foreach($recentHistory as $index => $log)
                                <tr>
                                    <td class="py-2 px-3 text-center">{{ $index + 1 }}</td>
                                    <td class="py-2 px-3 font-mono">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="py-2 px-3">
                                        @if(class_basename($log) === 'Call')
                                            Call
                                        @else
                                            {{ ucwords(str_replace('_', ' ', $log->activity_type)) }}
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 font-medium">{{ $log->notes ?? $log->description ?? '-' }}</td>
                                    <td class="py-2 px-3">{{ $log->user->name ?? 'System' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Footer Buttons -->
            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="{{ url()->previous() }}" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded shadow-sm text-[13px] border border-slate-300">Cancel</a>
                <button type="submit" class="bg-[#4A86BA] hover:bg-[#386b99] text-white font-bold py-1.5 px-6 rounded shadow text-[13px]">
                    Save Negotiation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
