@extends('layouts.reos')

@section('title', 'Company Settings - REOS')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="reos-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#2563EB]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Company Settings</span>
            </div>
            <h1 class="page-heading text-2xl font-extrabold text-slate-900">Real Estate Company Profile & Settings</h1>
            <p class="body-text text-xs text-slate-500 mt-0.5 font-medium">Configure corporate information, RERA license numbers, GST details, and payment gateways</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-lg">
            <span class="text-slate-900 font-mono">Company ID: #{{ $company->id ?? '1' }}</span>
        </div>
    </div>

    <!-- Company Settings Form -->
    <div class="reos-card p-6 space-y-6">
        <div class="border-b border-slate-100 pb-3">
            <h2 class="text-lg font-bold text-slate-900">Corporate Information</h2>
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

            <div class="border-b border-slate-100 pb-3 mt-6">
                <h2 class="text-lg font-bold text-slate-900">Attendance & GPS Settings</h2>
                <p class="text-xs text-slate-500">Configure base location and allowed radius for employee attendance verification.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="latitude" class="block text-slate-700 mb-1 font-bold">Base Latitude</label>
                    <input id="latitude" name="latitude" type="text" value="{{ old('latitude', $company->settings['latitude'] ?? '') }}" placeholder="e.g. 22.7196" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                </div>

                <div>
                    <label for="longitude" class="block text-slate-700 mb-1 font-bold">Base Longitude</label>
                    <input id="longitude" name="longitude" type="text" value="{{ old('longitude', $company->settings['longitude'] ?? '') }}" placeholder="e.g. 75.8577" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                </div>

                <div>
                    <label for="attendance_radius_km" class="block text-slate-700 mb-1 font-bold">Allowed Radius (in KM)</label>
                    <input id="attendance_radius_km" name="attendance_radius_km" type="number" step="0.1" value="{{ old('attendance_radius_km', $company->settings['attendance_radius_km'] ?? '30') }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                </div>
            </div>

            <div class="flex justify-start">
                <button type="button" id="detect-location-btn" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition border border-slate-300 flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Detect My Current Location</span>
                </button>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold rounded-lg shadow-xs transition cursor-pointer">
                    Save Company Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('detect-location-btn').addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
    }
    
    // Custom Popup Before Requesting
    const userAgreed = confirm("📍 Hum aapki current location fetch kar rahe hain.\n\nKripya dhyan dein:\n1. Agar browser aapse permission maange, toh 'Allow' par click karein.\n2. Agar location fail hoti hai, toh hum automatically network location use karenge.\n\nContinue karein?");
    if (!userAgreed) return;

    btn.innerHTML = 'Detecting...';
    btn.disabled = true;
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
            btn.innerHTML = originalText;
            btn.disabled = false;
        },
        (error) => {
            let errorMsg = 'Unable to retrieve location.\n';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg += "Error 1: User denied the request for Geolocation. (Browser or OS blocked it)";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg += "Error 2: Location information is unavailable. (Desktop PC without GPS/Wi-Fi)";
                    break;
                case error.TIMEOUT:
                    errorMsg += "Error 3: The request to get user location timed out.";
                    break;
                default:
                    errorMsg += "Error 0: An unknown error occurred. " + error.message;
                    break;
            }
            alert(errorMsg);
            console.error(error);
            btn.innerHTML = originalText;
            btn.disabled = false;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
});
</script>
@endsection
