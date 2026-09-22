@extends('layouts.reos')

@section('title', 'Drop Lead â€“ ' . $lead->first_name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center space-x-3 text-sm text-slate-500 mb-4">
        <a href="{{ route('leads.index') }}" class="hover:text-blue-600">Leads</a>
        <span>â€º</span>
        <a href="{{ route('leads.show', $lead->id) }}" class="hover:text-blue-600 font-semibold text-slate-800">{{ $lead->first_name }} {{ $lead->last_name }}</a>
        <span>â€º</span>
        <span class="text-rose-600 font-bold">Drop Lead</span>
    </div>

    <div class="bg-white w-full max-w-3xl overflow-hidden shadow-sm border border-slate-300">
        <div class="flex justify-between items-start px-5 py-4 bg-[#4A86BA] text-white relative">
            <div>
                <h3 class="text-xl font-bold mb-2">Drop / Disqualify Lead</h3>
                <div class="flex items-center space-x-6 text-[13px] font-semibold">
                    <div>Customer Name: <span class="text-blue-100">{{ $lead->first_name }} {{ $lead->last_name }}</span></div>
                    <div>Phone No. <span class="text-blue-100">{{ $lead->phone }}</span></div>
                </div>
            </div>
        </div>

        <form action="{{ route('leads.lost', $lead->id) }}" method="POST" class="p-5 text-sm space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Reason for Drop <span class="text-orange-500">*</span></label>
                    <select name="lost_reason" required class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                        <option value="">Select Reason</option>
                        <option value="Budget Issue">Budget Issue</option>
                        <option value="Location Mismatch">Location Mismatch</option>
                        <option value="Bought Elsewhere">Bought Elsewhere</option>
                        <option value="Fake / Invalid Number">Fake / Invalid Number</option>
                        <option value="Not Answering">Not Answering (Multiple Attempts)</option>
                        <option value="Postponed Buying Plan">Postponed Buying Plan</option>
                        <option value="Competitor Pricing">Competitor Pricing Better</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Additional Remarks / Feedback</label>
                <textarea name="lost_notes" rows="3" placeholder="Explain the specific reason in detail so marketing/management can analyze..." class="w-full bg-white border border-slate-300 p-2 focus:outline-none focus:border-blue-500 text-[13px] resize-none"></textarea>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="{{ url()->previous() }}" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded shadow-sm text-[13px] border border-slate-300">Cancel</a>
                <button type="submit" class="bg-[#4A86BA] hover:bg-[#386b99] text-white font-bold py-1.5 px-6 rounded shadow text-[13px]">
                    Mark as Dropped
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
