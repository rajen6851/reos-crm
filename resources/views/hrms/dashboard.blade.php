@extends('layouts.reos')

@section('title', 'HRMS Dashboard – UrbanProperty')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Banner & Breadcrumb -->
    <div class="bg-gradient-to-r from-blue-900 to-indigo-900 text-white p-6 rounded-2xl shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
        <div class="relative z-10">
            <div class="flex items-center space-x-2 text-xs font-semibold text-indigo-300 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-white transition">Home</a>
                <span>›</span>
                <span class="text-white font-bold">HRMS Dashboard</span>
            </div>
            <h1 class="page-heading text-3xl font-extrabold tracking-tight">
                @if(auth()->user()->isSaaSFounder())
                    Global HR Overview
                @else
                    HRMS Command Center
                @endif
            </h1>
            <p class="text-indigo-200 text-sm mt-1 max-w-xl">
                Real-time insights into your workforce's attendance, leaves, and payroll.
            </p>
        </div>
    </div>

    @if(auth()->user()->isSaaSFounder())
    <!-- SAAS FOUNDER MASTER HR OVERVIEW CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white/80 backdrop-blur border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition space-y-2 group">
            <div class="flex items-center justify-between text-xs font-extrabold text-slate-500 uppercase tracking-wider">
                <span>Total Network Staff</span>
                <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition"><i class="fa-solid fa-users-gear"></i></div>
            </div>
            <div class="text-3xl font-black text-slate-800 font-mono">{{ $totalPlatformStaff }}</div>
        </div>

        <div class="bg-white/80 backdrop-blur border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition space-y-2 group">
            <div class="flex items-center justify-between text-xs font-extrabold text-slate-500 uppercase tracking-wider">
                <span>Today's Check-ins</span>
                <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition"><i class="fa-solid fa-stopwatch"></i></div>
            </div>
            <div class="text-3xl font-black text-emerald-600 font-mono">{{ $todayPlatformCheckIns }}</div>
        </div>

        <div class="bg-white/80 backdrop-blur border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition space-y-2 group">
            <div class="flex items-center justify-between text-xs font-extrabold text-slate-500 uppercase tracking-wider">
                <span>Pending Approvals</span>
                <div class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center text-amber-600 group-hover:scale-110 transition"><i class="fa-solid fa-clock-rotate-left"></i></div>
            </div>
            <div class="text-3xl font-black text-amber-600 font-mono">{{ $pendingLeaveRequestsCount }}</div>
        </div>

        <div class="bg-white/80 backdrop-blur border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition space-y-2 group">
            <div class="flex items-center justify-between text-xs font-extrabold text-slate-500 uppercase tracking-wider">
                <span>Generated Payslips</span>
                <div class="w-8 h-8 rounded-full bg-rose-50 flex items-center justify-center text-rose-600 group-hover:scale-110 transition"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            </div>
            <div class="text-3xl font-black text-rose-600 font-mono">{{ $generatedSalarySlipsCount }}</div>
        </div>
    </div>
    @else
    <!-- BUILDER TENANT DAILY CLOCK-IN WIDGET -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if(!auth()->user()->isDirector() && !auth()->user()->isCompanyFounder() && !auth()->user()->isSaaSFounder() && !auth()->user()->isCompanyAdmin())
        <!-- Daily Clock-In / Clock-Out Hero Widget -->
        <div class="bg-[#0F172A] text-white p-6 rounded-2xl shadow-xl flex flex-col justify-between space-y-6 relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500 rounded-full blur-3xl opacity-20"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-extrabold text-indigo-300 uppercase tracking-wider">My Shift</span>
                    <span class="text-xs font-bold font-mono text-slate-300 bg-slate-800/80 px-3 py-1 rounded-full border border-slate-700">
                        {{ date('D, M j, Y') }}
                    </span>
                </div>

                <div class="mt-4 space-y-1">
                    @if($myTodayAttendance && $myTodayAttendance->clock_in)
                        <div class="text-3xl font-black text-emerald-400 font-mono flex items-center space-x-3 tracking-tight">
                            <span class="relative flex h-4 w-4">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500"></span>
                            </span>
                            <span>{{ date('h:i A', strtotime($myTodayAttendance->clock_in)) }}</span>
                        </div>
                        <div class="text-xs text-slate-300 font-semibold mt-2 bg-white/10 inline-block px-3 py-1 rounded-full border border-white/10">Mode: <span class="uppercase text-white tracking-widest">{{ str_replace('_', ' ', $myTodayAttendance->work_location) }}</span></div>
                    @else
                        <div class="text-3xl font-black text-amber-400 font-mono tracking-tight">Off the clock</div>
                        <div class="text-xs text-slate-400 font-semibold mt-1">Head over to the Attendance page to start your shift.</div>
                    @endif
                </div>
            </div>
            
            <div class="pt-4 border-t border-slate-800/50 space-y-3 relative z-10">
                <a href="{{ route('hrms.attendance') }}" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-user-clock"></i>
                    <span>Go to Attendance Page</span>
                </a>
            </div>
        </div>
        @endif
        
        <div class="bg-white/80 backdrop-blur border border-slate-200 p-6 rounded-2xl shadow-sm flex flex-col justify-between space-y-6">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900 mb-4"><i class="fa-solid fa-bolt text-amber-400 mr-2"></i>Quick Actions</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('hrms.leaves') }}" class="flex flex-col items-center justify-center p-6 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-2xl transition text-center text-indigo-700 font-bold text-sm h-full shadow-sm group">
                        <i class="fa-solid fa-calendar-plus text-3xl mb-3 group-hover:scale-110 transition"></i>
                        Leave Management
                    </a>
                    <a href="{{ route('hrms.payroll') }}" class="flex flex-col items-center justify-center p-6 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-2xl transition text-center text-emerald-700 font-bold text-sm h-full shadow-sm group">
                        <i class="fa-solid fa-file-invoice-dollar text-3xl mb-3 group-hover:scale-110 transition"></i>
                        Salary Slips
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Live Staff Location Map Widget -->
    @if(auth()->user()->isSaaSFounder() || auth()->user()->isCompanyAdmin() || auth()->user()->isDirector() || auth()->user()->isCompanyFounder())
    <div class="bg-white/80 backdrop-blur border border-slate-200 p-0 rounded-2xl shadow-lg mt-6 relative h-[450px] overflow-hidden flex flex-col">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex flex-col sm:flex-row items-center justify-between z-10">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900"><i class="fa-solid fa-map-location-dot text-indigo-600 mr-2"></i>Staff Field Map</h3>
                <p class="text-[11px] text-slate-500 font-semibold mt-0.5">Visualize where your staff checked in from</p>
            </div>
            
            <form action="{{ route('hrms.dashboard') }}" method="GET" class="mt-3 sm:mt-0 flex items-center space-x-2">
                <input type="date" name="map_date" value="{{ $mapDate }}" class="border-slate-200 rounded-lg text-xs font-bold text-slate-700 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 px-3">
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition">Filter Map</button>
            </form>
        </div>
        
        <div id="dashboardMap" class="w-full h-full bg-slate-100 flex flex-col items-center justify-center text-slate-400 relative z-0">
            <i class="fa-solid fa-spinner fa-spin text-4xl mb-3"></i>
            <span class="text-sm font-bold tracking-widest uppercase">Loading Map Engine...</span>
        </div>
    </div>
    
    <script>
        function initDashboardMap() {
            const locations = [
                @foreach($todayRoster as $att)
                    @if($att->latitude && $att->longitude)
                        {
                            lat: {{ $att->latitude }},
                            lng: {{ $att->longitude }},
                            name: "{{ addslashes($att->user->name) }}",
                            time: "{{ date('h:i A', strtotime($att->clock_in)) }}",
                            avatar: "{{ substr($att->user->name, 0, 1) }}"
                        },
                    @endif
                @endforeach
            ];

            const mapOptions = {
                zoom: locations.length > 0 ? 11 : 4,
                center: locations.length > 0 ? { lat: locations[0].lat, lng: locations[0].lng } : { lat: 20.5937, lng: 78.9629 },
                mapId: "DEMO_MAP_ID",
                disableDefaultUI: false,
                styles: [
                    { "featureType": "water", "elementType": "geometry", "stylers": [{ "color": "#e9e9e9" }, { "lightness": 17 }] },
                    { "featureType": "landscape", "elementType": "geometry", "stylers": [{ "color": "#f5f5f5" }, { "lightness": 20 }] }
                ]
            };

            const map = new google.maps.Map(document.getElementById("dashboardMap"), mapOptions);
            const infoWindow = new google.maps.InfoWindow();

            locations.forEach((loc, i) => {
                const marker = new google.maps.Marker({
                    position: { lat: loc.lat, lng: loc.lng },
                    map: map,
                    title: loc.name,
                    animation: google.maps.Animation.DROP
                });

                marker.addListener("click", () => {
                    const content = 
                        <div class="p-2 min-w-[150px]">
                            <div class="flex items-center space-x-3 border-b border-slate-100 pb-2 mb-2">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">+loc.avatar+</div>
                                <div class="font-bold text-sm text-slate-900"> + loc.name + </div>
                            </div>
                            <div class="text-xs text-emerald-600 font-extrabold flex items-center"><i class="fa-solid fa-clock mr-1.5"></i> + loc.time + </div>
                        </div>
                    ;
                    infoWindow.setContent(content);
                    infoWindow.open(map, marker);
                });
            });
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initDashboardMap"></script>
    @endif
</div>
@endsection
