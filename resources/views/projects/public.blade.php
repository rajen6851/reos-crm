<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->name }} – Project Showcase</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>

<body class="text-slate-800 bg-slate-50 antialiased min-h-screen flex flex-col justify-between">

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40 shadow-xs">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-extrabold flex items-center justify-center shadow-sm text-lg">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-slate-900 leading-tight">{{ $project->name }}</h1>
                    <p class="text-xs text-slate-500 font-medium">
                        By <span
                            class="text-indigo-600 font-semibold">{{ $project->company->name ?? 'REOS Developer' }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <a href="#inquireForm"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition duration-150 flex items-center space-x-1">
                    <i class="fa-solid fa-phone mr-1"></i><span>Contact Sales</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-5xl mx-auto px-4 py-6 space-y-6 w-full flex-1">

        @if(session('inquiry_success'))
            <div
                class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between text-xs font-semibold shadow-2xs">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i>
                    <span>{{ session('inquiry_success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()"
                    class="text-emerald-600 hover:text-emerald-900 font-bold ml-4">✕</button>
            </div>
        @endif

        @if($broker)
            <div
                class="p-3.5 bg-indigo-50/90 border border-indigo-200 rounded-2xl flex items-center justify-between text-xs text-indigo-950 shadow-2xs">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold flex items-center justify-center">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                    <div>
                        <span class="text-[11px] text-indigo-600 font-bold uppercase tracking-wider block">Recommended
                            Partner</span>
                        <span class="font-bold text-slate-900 text-xs">{{ $broker->agency_name ?? $broker->name }}</span>
                        @if($broker->phone)
                            <span class="text-slate-500"> • {{ $broker->phone }}</span>
                        @endif
                    </div>
                </div>
                <span
                    class="px-2.5 py-1 bg-white border border-indigo-200 text-indigo-700 text-[10px] font-mono font-bold rounded-lg shadow-2xs">Verified
                    Broker</span>
            </div>
        @endif

        <!-- Banner Image & Quick Badges -->
        <div class="relative rounded-3xl overflow-hidden bg-slate-900 shadow-md border border-slate-200/80">
            <img src="{{ $project->banner_image ?? '/uploads/projects/default_project.jpg' }}"
                alt="{{ $project->name }}" class="w-full h-64 md:h-80 object-cover opacity-90">
            <div
                class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent flex flex-col justify-end p-6 md:p-8 text-white space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="bg-indigo-600 text-white font-mono text-[10px] font-extrabold px-2.5 py-1 rounded-md uppercase tracking-wider">
                        {{ $project->code }}
                    </span>
                    <span
                        class="bg-slate-800/80 backdrop-blur-xs text-slate-200 font-mono text-[10px] font-bold px-2.5 py-1 rounded-md border border-slate-700">
                        RERA: {{ $project->rera_number ?? 'Approved' }}
                    </span>
                    @if($availableUnitsCount > 0)
                        <span class="bg-emerald-500 text-white font-bold text-[10px] px-2.5 py-1 rounded-md shadow-xs">
                            <i class="fa-solid fa-bolt text-white mr-1"></i>{{ $availableUnitsCount }} Units Ready / Available
                        </span>
                    @endif
                </div>
                <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">{{ $project->name }}</h2>
                <p class="text-xs md:text-sm text-slate-300 flex items-center space-x-1 font-medium">
                    <i class="fa-solid fa-location-dot text-indigo-400 mr-1"></i>
                    <span>{{ $project->location_address ? $project->location_address . ', ' : '' }}{{ $project->city }}</span>
                </p>
            </div>
        </div>

        <!-- Key Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs space-y-1">
                <span class="text-slate-400 font-semibold block text-[11px]">Configurations</span>
                <div class="text-sm md:text-base font-extrabold text-slate-900">
                    {{ $unitTypes->count() > 0 ? implode(', ', $unitTypes->toArray()) : '2BHK, 3BHK' }}
                </div>
            </div>

            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs space-y-1">
                <span class="text-slate-400 font-semibold block text-[11px]">Carpet Area</span>
                <div class="text-sm md:text-base font-extrabold text-slate-900 font-mono">
                    @if($minCarpetArea && $maxCarpetArea && $minCarpetArea != $maxCarpetArea)
                        {{ number_format($minCarpetArea) }} - {{ number_format($maxCarpetArea) }} <span
                            class="text-xs font-sans font-normal text-slate-500">sq.ft</span>
                    @elseif($minCarpetArea)
                        {{ number_format($minCarpetArea) }} <span
                            class="text-xs font-sans font-normal text-slate-500">sq.ft</span>
                    @else
                        1200 - 1850 <span class="text-xs font-sans font-normal text-slate-500">sq.ft</span>
                    @endif
                </div>
            </div>

            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs space-y-1">
                <span class="text-slate-400 font-semibold block text-[11px]">Total Towers</span>
                <div class="text-sm md:text-base font-extrabold text-indigo-600 font-mono">
                    {{ $project->buildings->count() > 0 ? $project->buildings->count() . ' Towers' : 'Multi-Tower Phase' }}
                </div>
            </div>

            <div class="p-4 bg-emerald-50/70 border border-emerald-200/80 rounded-2xl shadow-2xs space-y-1">
                <span class="text-emerald-700 font-semibold block text-[11px]">Starting Price</span>
                <div class="text-sm md:text-base font-extrabold text-emerald-900 font-mono">
                    @if($minPrice && $minPrice > 0)
                        ₹{{ number_format($minPrice / 100000, 2) }} Lakhs*
                    @else
                        Request Price Quote
                    @endif
                </div>
            </div>
        </div>

        <!-- Towers & Building Overview -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-building text-indigo-600"></i>
                    <span>Towers & Building Structure</span>
                </h3>
                <span class="text-xs text-slate-500 font-mono">{{ $project->buildings->count() }} Active
                    Buildings</span>
            </div>

            @if($project->buildings->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($project->buildings as $bld)
                        <div
                            class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-indigo-300 transition space-y-3 flex flex-col justify-between">
                            <div class="space-y-1">
                                <div class="flex justify-between items-center">
                                    <span
                                        class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-mono font-bold text-[10px] uppercase border border-indigo-200">{{ $bld->code }}</span>
                                    <span class="text-xs text-slate-500 font-mono">{{ $bld->total_floors }} Floors</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 mt-1">{{ $bld->name }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Configurations:
                                    <strong class="text-slate-700">
                                        {{ $bld->units->pluck('unit_type')->unique()->implode(', ') ?: 'Standard Residential Units' }}
                                    </strong>
                                </p>
                            </div>

                            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                                <span class="text-emerald-700 font-semibold text-[11px]">
                                    <i class="fa-solid fa-circle text-emerald-500 mr-1 text-[8px]"></i>{{ $bld->units->where('status', 'available')->count() }} Units Available
                                </span>
                                <a href="#inquireForm"
                                    onclick="setInterestedUnitType('{{ $bld->units->first()->unit_type ?? '' }}')"
                                    class="text-indigo-600 hover:text-indigo-800 font-bold text-xs">Inquire Tower →</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center text-xs text-slate-500 bg-slate-50 rounded-2xl">
                    Tower details and inventory layouts are available on direct request.
                </div>
            @endif
        </div>

        <!-- Project Amenities -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-2xs space-y-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                <span>Key Project Amenities</span>
            </h3>

            @php
                $amenities = $project->amenities ?? ['Grand Clubhouse', 'Swimming Pool', 'Equipped Gymnasium', 'EV Charging Station', '24x7 Security & CCTV', 'Children Play Area', 'Landscaped Gardens', 'Power Backup'];
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                @foreach($amenities as $item)
                    <div
                        class="p-3 bg-slate-50 rounded-xl border border-slate-200/70 flex items-center space-x-2 text-slate-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span>{{ $item }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Inquiry & Callback Request Form -->
        <!-- <div id="inquireForm" class="bg-gradient-to-br from-indigo-900 via-slate-900 to-slate-950 rounded-3xl p-6 md:p-8 text-white shadow-xl space-y-6">
            <div class="space-y-1">
                <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-mono font-bold rounded-full uppercase">Get Direct Quote</span>
                <h3 class="text-xl md:text-2xl font-extrabold text-white">Interested in {{ $project->name }}?</h3>
                <p class="text-xs text-slate-400">Request pricing details, floor plans, or schedule an exclusive site visit.</p>
            </div>

            <form method="POST" action="{{ route('projects.public.inquire', $project->id) }}" class="space-y-4 text-xs">
                @csrf
                @if($broker)
                <input type="hidden" name="broker_id" value="{{ $broker->id }}">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Your Full Name *</label>
                        <input type="text" name="name" required placeholder="John Doe" 
                               class="w-full bg-slate-800/90 border border-slate-700 text-white rounded-xl p-3 focus:outline-none focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Mobile / Phone Number *</label>
                        <input type="tel" name="phone" required placeholder="+91 98765 43210" 
                               class="w-full bg-slate-800/90 border border-slate-700 text-white rounded-xl p-3 focus:outline-none focus:border-indigo-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Email Address (Optional)</label>
                        <input type="email" name="email" placeholder="john@example.com" 
                               class="w-full bg-slate-800/90 border border-slate-700 text-white rounded-xl p-3 focus:outline-none focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Preferred Unit Type</label>
                        <select name="interested_unit_type" id="unitTypeSelect" 
                                class="w-full bg-slate-800/90 border border-slate-700 text-white rounded-xl p-3 focus:outline-none focus:border-indigo-400">
                            <option value="">Select Preferred Type</option>
                            @foreach($unitTypes as $type)
                            <option value="{{ $type }}">{{ $type }} Apartment</option>
                            @endforeach
                            <option value="General Inquiry">General Project Inquiry</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Specific Requirements / Message</label>
                    <textarea name="notes" rows="2" placeholder="Looking for higher floor, east facing unit, etc." 
                              class="w-full bg-slate-800/90 border border-slate-700 text-white rounded-xl p-3 focus:outline-none focus:border-indigo-400"></textarea>
                </div>

                <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-extrabold rounded-xl transition shadow-lg flex items-center justify-center space-x-2 cursor-pointer">
                    <i class="fa-solid fa-paper-plane mr-1"></i>
                    <span>Submit Inquiry & Request Call Back</span>
                </button>
            </form>
        </div> -->

        <!-- Share Options Bar -->
        <div
            class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-2xs flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
            <div>
                <h4 class="font-bold text-slate-900">Share this Project with Friends & Family</h4>
                <p class="text-slate-500 text-[11px]">Anyone with this link can view project details directly.</p>
            </div>

            <div class="flex items-center space-x-2.5 w-full md:w-auto">
                <button onclick="shareWhatsApp()"
                    class="flex-1 md:flex-none px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-xs flex items-center justify-center space-x-1.5 cursor-pointer">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Share on WhatsApp</span>
                </button>
                <button onclick="copyCurrentLink()"
                    class="flex-1 md:flex-none px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl border border-slate-300/80 transition flex items-center justify-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-link"></i>
                    <span id="copyBtnText">Copy Link</span>
                </button>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-8 py-4 text-center text-xs text-slate-500">
        <div class="max-w-5xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>© {{ date('Y') }} {{ $project->company->name ?? 'REOS' }}. All Rights Reserved.</span>
            <span class="text-[11px] text-slate-400 font-mono">Powered by REOS Real Estate OS</span>
        </div>
    </footer>

    <script>
        function setInterestedUnitType(type) {
            if (type) {
                const select = document.getElementById('unitTypeSelect');
                if (select) select.value = type;
            }
        }

        function copyCurrentLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const btnText = document.getElementById('copyBtnText');
                btnText.innerHTML = '<i class="fa-solid fa-check mr-1"></i>Link Copied!';
                setTimeout(() => { btnText.innerHTML = 'Copy Link'; }, 2500);
            });
        }

        function shareWhatsApp() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent("Check out {{ $project->name }} in {{ $project->city }}!\nDetails & Pricing link: ");
            window.open(`https://api.whatsapp.com/send?text=${text}${url}`, '_blank');
        }
    </script>
</body>

</html>