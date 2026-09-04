@extends('layouts.reos')

@section('title', 'Company Settings - REOS')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-gear text-2xl text-indigo-600"></i>
                <h1 class="text-2xl font-black text-slate-900">Real Estate Company Profile & Settings</h1>
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium">Configure corporate information, RERA license numbers, GST details, and payment gateways</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 bg-indigo-50 border border-indigo-200 px-3.5 py-2 rounded-2xl">
            <span class="text-indigo-900">Company ID: #{{ $company->id ?? '1' }}</span>
        </div>
    </div>

    <!-- Company Settings Form -->
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-6">
        <div class="border-b border-slate-100 pb-3">
            <h2 class="text-lg font-black text-slate-900">Corporate Information</h2>
            <p class="text-xs text-slate-500">Legal business entity details used in tax invoices and buyer agreements.</p>
        </div>

        <form method="post" action="{{ route('company-settings.update') }}" class="space-y-4 text-xs">
            @csrf
            @method('put')

            <div>
                <label for="name" class="block text-slate-700 mb-1 font-bold">Company Legal Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name', $company->name ?? '') }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-slate-700 mb-1 font-bold">Official Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $company->email ?? '') }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                </div>

                <div>
                    <label for="phone" class="block text-slate-700 mb-1 font-bold">Official Phone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $company->phone ?? '') }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="rera_number" class="block text-slate-700 mb-1 font-bold">RERA Registration License No.</label>
                    <input id="rera_number" name="rera_number" type="text" value="{{ old('rera_number', $company->rera_number ?? 'P02400008812') }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-mono font-bold focus:outline-none focus:border-indigo-600">
                </div>

                <div>
                    <label for="gstin" class="block text-slate-700 mb-1 font-bold">GSTIN Tax Registration No.</label>
                    <input id="gstin" name="gstin" type="text" value="{{ old('gstin', $company->gstin ?? '36AAACR1001A1Z0') }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-mono font-bold focus:outline-none focus:border-indigo-600">
                </div>
            </div>

            <div>
                <label for="address" class="block text-slate-700 mb-1 font-bold">Corporate Office Address</label>
                <textarea id="address" name="address" rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">{{ old('address', $company->address ?? 'Plot No. 42, Real Estate Enclave, Financial District, Jubilee Hills') }}</textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl shadow-xs transition">
                    Save Company Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
