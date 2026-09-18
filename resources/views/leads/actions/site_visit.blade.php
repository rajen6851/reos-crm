@extends('layouts.reos')

@section('title', 'Schedule Site Visit – ' . $lead->first_name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center space-x-3 text-sm text-slate-500 mb-4">
        <a href="{{ route('leads.index') }}" class="hover:text-blue-600">Leads</a>
        <span>›</span>
        <a href="{{ route('leads.show', $lead->id) }}" class="hover:text-blue-600 font-semibold text-slate-800">{{ $lead->first_name }} {{ $lead->last_name }}</a>
        <span>›</span>
        <span class="text-indigo-600 font-bold">Schedule Site Visit</span>
    </div>

    <div class="bg-white w-full max-w-4xl overflow-hidden shadow-sm border border-slate-300">
        <div class="flex justify-between items-start px-5 py-4 bg-[#4A86BA] text-white relative">
            <div>
                <h3 class="text-xl font-bold mb-2">Schedule Site Visit</h3>
                <div class="flex items-center space-x-6 text-[13px] font-semibold">
                    <div>Customer Name: <span class="text-blue-100">{{ $lead->first_name }} {{ $lead->last_name }}</span></div>
                    <div>Phone No. <span class="text-blue-100">{{ $lead->phone }}</span></div>
                </div>
            </div>
        </div>

        <form action="{{ route('leads.site-visit', $lead->id) }}" method="POST" class="p-5 text-sm space-y-6">
            @csrf

            <!-- Section: Primary Details -->
            <div>
                <div class="bg-[#DCEBF6] font-bold text-center text-[#1F2937] py-1 text-[14px] border border-slate-300 border-b-0">
                    Primary Details
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border border-slate-300 p-4 bg-slate-50">
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Project <span class="text-orange-500">*</span></label>
                        <select name="sv_project_id" required class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                            <option value="">Select a Project</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ $lead->interested_project_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Scheduled Date & Time <span class="text-orange-500">*</span></label>
                        <input type="datetime-local" name="sv_scheduled_at" required class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                    </div>

                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Site Visited By <span class="text-orange-500">*</span></label>
                        <select name="sv_assigned_to" required class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                            <option value="">Select Executive</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ auth()->id() == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Pickup Location (if any)</label>
                        <input type="text" name="pickup_location" placeholder="e.g. Client Office / Home" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                    </div>
                </div>
            </div>

            <!-- Section: Broker Details -->
            <div>
                <div class="bg-[#DCEBF6] font-bold text-center text-[#1F2937] py-1 text-[14px] border border-slate-300 border-b-0 mt-2">
                    Broker Involvement (Optional)
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border border-slate-300 p-4 bg-slate-50">
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Broker Name</label>
                        <input type="text" name="sv_broker_name" placeholder="Name" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                    </div>
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Broker Phone</label>
                        <input type="text" name="sv_broker_phone" placeholder="Phone" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                    </div>
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Broker Company</label>
                        <input type="text" name="sv_broker_company" placeholder="Agency" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                    </div>
                </div>
            </div>

            <!-- Section: Remarks -->
            <div>
                <div class="bg-[#DCEBF6] font-bold text-center text-[#1F2937] py-1 text-[14px] border border-slate-300 border-b-0 mt-2">
                    Notes & Remarks
                </div>
                <div class="border border-slate-300 p-4 bg-slate-50 space-y-4">
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Visit Description</label>
                        <textarea name="sv_visit_description" rows="2" placeholder="Enter Visit Description" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px] resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Remark 1</label>
                            <input type="text" name="remark_1" placeholder="Enter Remark" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                        </div>
                        <div>
                            <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Remark 2</label>
                            <input type="text" name="remark_2" placeholder="Enter Remark" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Follow-up -->
            <div>
                <div class="bg-[#DCEBF6] font-bold text-center text-[#1F2937] py-1 text-[14px] border border-slate-300 border-b-0 mt-2">
                    Follow-up
                </div>
                <div class="border border-slate-300 p-4 bg-slate-50 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Inquiry Status <span class="text-orange-500">*</span></label>
                            <select name="inquiry_status" required class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                                <option value="IN FOLLOWUP" {{ $lead->status == 'in_followup' ? 'selected' : '' }}>IN FOLLOWUP</option>
                                <option value="UNDER NEGOTIATION" {{ $lead->status == 'negotiation' ? 'selected' : '' }}>UNDER NEGOTIATION</option>
                                <option value="SITE VISIT" selected>SITE VISIT</option>
                                <option value="BOOKED">BOOKED</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Next Followup Dt. <span class="text-orange-500">*</span></label>
                            <input type="datetime-local" name="next_followup_date" required class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[#1F2937] font-bold mb-1 text-[13px]">Followup Remark</label>
                        <textarea name="followup_remark" rows="2" placeholder="Enter Followup Remark" class="w-full bg-white border border-slate-300 p-1.5 focus:outline-none focus:border-blue-500 text-[13px] resize-none"></textarea>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="{{ url()->previous() }}" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded shadow-sm text-[13px] border border-slate-300">Cancel</a>
                <button type="submit" class="bg-[#4A86BA] hover:bg-[#386b99] text-white font-bold py-1.5 px-6 rounded shadow text-[13px]">
                    Schedule Site Visit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
