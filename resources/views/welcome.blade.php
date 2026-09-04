<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>REOS – Real Estate Operating System SaaS</title>
    
    <!-- Google Fonts Manrope & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons & TailwindCSS CDN & Alpine.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Manrope', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: '#059669',
                        branddark: '#047857',
                        navy: '#0F172A',
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Manrope', sans-serif; background-color: #F8FAFC; color: #0F172A; }
        
        /* Modern Gradient Animations */
        .gradient-text {
            background: linear-gradient(135deg, #059669 0%, #10B981 50%, #047857 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-glow {
            background: radial-gradient(circle at 50% 20%, rgba(16, 185, 129, 0.12) 0%, rgba(248, 250, 252, 0) 70%);
        }

        .reos-glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 30px -10px rgba(5, 150, 105, 0.08), 0 10px 15px -3px rgba(15, 23, 42, 0.04);
            border-color: #A7F3D0;
        }
    </style>
</head>
<body class="bg-[#F8FAFC] text-[#0F172A] selection:bg-[#059669] selection:text-white">

    <!-- Sticky Glassmorphic Navbar -->
    <header class="fixed top-0 left-0 right-0 z-50 reos-glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-2xl overflow-hidden shadow-xs border border-emerald-200 bg-white flex items-center justify-center p-0.5 group-hover:scale-105 transition transform">
                    <img src="{{ asset('images/logo.jpg') }}" alt="REOS Logo" class="w-full h-full object-cover rounded-xl">
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-[#0F172A]">REOS <span class="text-[#059669]">CRM</span></span>
                    <span class="text-[9px] uppercase font-bold tracking-widest text-emerald-600 block -mt-1">Real Estate Operating System</span>
                </div>
            </a>

            <!-- Center Navigation Links -->
            <nav class="hidden md:flex items-center space-x-8 text-xs font-bold text-slate-600">
                <a href="#features" class="hover:text-[#059669] transition">Platform Features</a>
                <a href="#inventory" class="hover:text-[#059669] transition">Inventory Locking</a>
                <a href="#broker-portal" class="hover:text-[#059669] transition">Broker Network</a>
                <a href="#pricing" class="hover:text-[#059669] transition">SaaS Pricing</a>
                <a href="#demo-accounts" class="hover:text-[#059669] transition">Demo Roles</a>
            </nav>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-[#059669] hover:bg-[#047857] text-white font-extrabold text-xs rounded-xl shadow-md hover:shadow-emerald-200 transition flex items-center space-x-2">
                            <span>Go to Dashboard</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-extrabold text-slate-700 hover:text-[#059669] transition px-4 py-2 rounded-xl border border-slate-200 hover:bg-white">
                            Sign In
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold text-xs rounded-xl shadow-md hover:shadow-emerald-200 transition flex items-center space-x-2">
                                <span>Register Business</span>
                                <i class="fa-solid fa-sparkles text-[10px]"></i>
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 md:pt-40 md:pb-28 hero-glow">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center space-y-8">
            <!-- Badge -->
            <div class="inline-flex items-center space-x-2.5 px-4 py-2 rounded-full text-xs font-extrabold bg-emerald-50 text-[#059669] border border-emerald-200 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>REOS 2.0 – Enterprise Multi-Tenant SaaS Platform</span>
            </div>

            <!-- Grand Headline -->
            <h1 class="text-4xl sm:text-6xl md:text-7xl font-extrabold text-[#0F172A] tracking-tight leading-[1.15]">
                The Complete Operating System for <br class="hidden sm:inline" />
                <span class="gradient-text">Real Estate Developers & Agencies</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-base sm:text-lg text-slate-600 max-w-3xl mx-auto leading-relaxed font-medium">
                Streamline property inventory pessimistic row locking, CRM lead pipelines, site visit tracking, channel partner broker commissions, automated payment schedules, and KYC document vaults in one unified workspace.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold text-sm rounded-2xl shadow-lg hover:shadow-emerald-300/40 transition transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                    <span>Register Your Real Estate Business</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-white hover:bg-slate-50 text-[#0F172A] border border-slate-200 font-extrabold text-sm rounded-2xl shadow-2xs transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-[#059669] fa-lock"></i>
                    <span>1-Click Role Login Demo</span>
                </a>
            </div>

            <!-- Feature Guarantee Badges -->
            <div class="flex flex-wrap items-center justify-center gap-6 pt-4 text-xs font-bold text-slate-500">
                <span class="flex items-center space-x-2">
                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                    <span>100% Tenant Column Scoping</span>
                </span>
                <span class="flex items-center space-x-2">
                    <i class="fa-solid fa-lock text-emerald-600"></i>
                    <span>Concurrency-Safe Unit Locks</span>
                </span>
                <span class="flex items-center space-x-2">
                    <i class="fa-solid fa-mobile-screen-button text-emerald-600"></i>
                    <span>Sanctum REST API Ready</span>
                </span>
            </div>

            <!-- Interactive Mock Dashboard Preview Window -->
            <div class="pt-8 max-w-5xl mx-auto">
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xl p-4 sm:p-6 text-left relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500/5 to-teal-500/0 pointer-events-none"></div>
                    
                    <!-- Browser Window Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full bg-rose-400"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                            <span class="text-[11px] font-mono font-bold text-slate-400 pl-2">app.reos.in / dashboard / live-inventory</span>
                        </div>
                        <div class="flex items-center space-x-2 text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Live Inventory Engine Connected</span>
                        </div>
                    </div>

                    <!-- Dashboard Mock Cards Row -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                            <div class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Active Inventory</div>
                            <div class="text-xl font-extrabold text-[#0F172A] font-mono mt-1">128 Units</div>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 inline-block mt-2">84 Available</span>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                            <div class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Pessimistic Locks</div>
                            <div class="text-xl font-extrabold text-amber-600 font-mono mt-1">14 On Hold</div>
                            <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200 inline-block mt-2">15 Min Hold Window</span>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                            <div class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">CRM Lead Pipeline</div>
                            <div class="text-xl font-extrabold text-[#0F172A] font-mono mt-1">482 Active</div>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 inline-block mt-2">94 Hot Score</span>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                            <div class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Broker Payouts</div>
                            <div class="text-xl font-extrabold text-emerald-700 font-mono mt-1">₹4.85 Lakh</div>
                            <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-md border border-teal-200 inline-block mt-2">Auto Calculated</span>
                        </div>
                    </div>

                    <!-- Mock Inventory Grid Preview -->
                    <div class="bg-slate-900 rounded-2xl p-4 text-white font-mono text-xs space-y-3">
                        <div class="flex justify-between items-center text-[11px] text-slate-400 pb-2 border-b border-slate-800">
                            <span>Apex Tower A – Interactive Unit Status Matrix</span>
                            <div class="flex space-x-3 text-[10px]">
                                <span class="text-emerald-400">● Available</span>
                                <span class="text-amber-400">● Hold</span>
                                <span class="text-rose-400">● Booked</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-4 sm:grid-cols-8 gap-2 text-center text-[11px] font-bold">
                            <div class="p-2.5 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">101 (2BHK)</div>
                            <div class="p-2.5 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">102 (3BHK)</div>
                            <div class="p-2.5 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/40">103 (HOLD)</div>
                            <div class="p-2.5 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/40">104 (BOOKED)</div>
                            <div class="p-2.5 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">201 (2BHK)</div>
                            <div class="p-2.5 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">202 (Villa)</div>
                            <div class="p-2.5 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/40">203 (HOLD)</div>
                            <div class="p-2.5 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/40">204 (BOOKED)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Platform Modules Grid -->
    <section id="features" class="py-20 bg-white border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-16">
            <div class="text-center space-y-4 max-w-3xl mx-auto">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-emerald-50 text-[#059669] border border-emerald-200">
                    <i class="fa-solid fa-cubes"></i>
                    <span>Core Platform Modules</span>
                </div>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-[#0F172A] tracking-tight">
                    Engineered Specifically for <span class="gradient-text">Real Estate Workflows</span>
                </h2>
                <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                    REOS replaces fragmented spreadsheets and generic CRMs with specialized real-estate tools.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Module 1 -->
                <div class="bg-[#F8FAFC] p-8 rounded-3xl border border-slate-200 card-hover space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-[#059669] flex items-center justify-center text-2xl font-bold border border-emerald-200">
                        <i class="fa-solid fa-building-user"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-[#0F172A]">Multi-Tenant Isolation</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Column-based `company_id` Eloquent scoping (`TenantScope`) ensuring 100% data privacy between developer companies and custom RBAC roles.
                    </p>
                </div>

                <!-- Module 2 -->
                <div class="bg-[#F8FAFC] p-8 rounded-3xl border border-slate-200 card-hover space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl font-bold border border-amber-200">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-[#0F172A]">Pessimistic Inventory Lock</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Database row-level locking (`lockForUpdate()`) preventing double-booking race conditions across sales teams during high-demand property launches.
                    </p>
                </div>

                <!-- Module 3 -->
                <div class="bg-[#F8FAFC] p-8 rounded-3xl border border-slate-200 card-hover space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center text-2xl font-bold border border-teal-200">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-[#0F172A]">Broker Channel Subsystem</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Sanitized partner portal for brokers to submit leads, monitor sanitized status progress, track commissions, and receive batch payouts.
                    </p>
                </div>

                <!-- Module 4 -->
                <div class="bg-[#F8FAFC] p-8 rounded-3xl border border-slate-200 card-hover space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl font-bold border border-purple-200">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-[#0F172A]">Cost Sheets & Agreements</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Instant calculation of base price, PLC charges, GST, statutory breakdowns, payment milestone schedules, and agreement skip request approvals.
                    </p>
                </div>

                <!-- Module 5 -->
                <div class="bg-[#F8FAFC] p-8 rounded-3xl border border-slate-200 card-hover space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl font-bold border border-blue-200">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-[#0F172A]">KYC & Document Vault</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Centralized document vault for customer KYC proofs, agreement execution PDFs, property floor plans, and downloadable PDF payment receipts.
                    </p>
                </div>

                <!-- Module 6 -->
                <div class="bg-[#F8FAFC] p-8 rounded-3xl border border-slate-200 card-hover space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center text-2xl font-bold border border-rose-200">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-[#0F172A]">Team Support Desk</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        In-house ticketing system (`/support-tickets`) with priority tagging, category assignment, and reply threads to resolve field issues instantly.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Role Login Presets Bar -->
    <section id="demo-accounts" class="py-16 bg-[#F8FAFC] border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-8">
            <div class="text-center space-y-2">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-[#059669] border border-emerald-200">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Instant Live Demo Access</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-[#0F172A]">Explore Pre-seeded Role Dashboards</h3>
                <p class="text-xs text-slate-500">All demo accounts use default password: <code class="font-mono font-bold bg-slate-200 px-2 py-0.5 rounded text-[#059669]">password123</code></p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <a href="{{ route('login') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-emerald-300 transition text-center space-y-2 group shadow-2xs">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 font-bold mx-auto flex items-center justify-center group-hover:scale-110 transition">
                        <i class="fa-solid fa-crown text-sm"></i>
                    </div>
                    <div class="text-xs font-extrabold text-[#0F172A]">SaaS Founder</div>
                    <div class="text-[10px] text-slate-400 font-mono">founder@reos.com</div>
                </a>

                <a href="{{ route('login') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-emerald-300 transition text-center space-y-2 group shadow-2xs">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 font-bold mx-auto flex items-center justify-center group-hover:scale-110 transition">
                        <i class="fa-solid fa-building-columns text-sm"></i>
                    </div>
                    <div class="text-xs font-extrabold text-[#0F172A]">Director</div>
                    <div class="text-[10px] text-slate-400 font-mono">director@apexrealty.com</div>
                </a>

                <a href="{{ route('login') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-emerald-300 transition text-center space-y-2 group shadow-2xs">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#059669] font-bold mx-auto flex items-center justify-center group-hover:scale-110 transition">
                        <i class="fa-solid fa-user-tie text-sm"></i>
                    </div>
                    <div class="text-xs font-extrabold text-[#0F172A]">Company Admin</div>
                    <div class="text-[10px] text-slate-400 font-mono">admin@apexrealty.com</div>
                </a>

                <a href="{{ route('login') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-emerald-300 transition text-center space-y-2 group shadow-2xs">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 font-bold mx-auto flex items-center justify-center group-hover:scale-110 transition">
                        <i class="fa-solid fa-chart-line text-sm"></i>
                    </div>
                    <div class="text-xs font-extrabold text-[#0F172A]">Sales Manager</div>
                    <div class="text-[10px] text-slate-400 font-mono">manager@apexrealty.com</div>
                </a>

                <a href="{{ route('login') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-emerald-300 transition text-center space-y-2 group shadow-2xs">
                    <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-700 font-bold mx-auto flex items-center justify-center group-hover:scale-110 transition">
                        <i class="fa-solid fa-briefcase text-sm"></i>
                    </div>
                    <div class="text-xs font-extrabold text-[#0F172A]">Sales Exec</div>
                    <div class="text-[10px] text-slate-400 font-mono">sales@apexrealty.com</div>
                </a>

                <a href="{{ route('login') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-emerald-300 transition text-center space-y-2 group shadow-2xs">
                    <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 font-bold mx-auto flex items-center justify-center group-hover:scale-110 transition">
                        <i class="fa-solid fa-handshake text-sm"></i>
                    </div>
                    <div class="text-xs font-extrabold text-[#0F172A]">Broker Partner</div>
                    <div class="text-[10px] text-slate-400 font-mono">broker@apexrealty.com</div>
                </a>
            </div>
        </div>
    </section>

    <!-- Pricing / Tiers Section -->
    <section id="pricing" class="py-20 bg-white border-t border-slate-200/80">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 space-y-12 text-center">
            <div class="space-y-4">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-emerald-50 text-[#059669] border border-emerald-200">
                    <i class="fa-solid fa-gem"></i>
                    <span>Simple SaaS Subscriptions</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#0F172A]">Flexible Plans for Growing Developers</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left max-w-3xl mx-auto">
                <!-- Starter Plan -->
                <div class="bg-[#F8FAFC] p-8 rounded-3xl border border-slate-200 space-y-6 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-extrabold uppercase text-slate-400 tracking-wider">Starter Tier</span>
                        <div class="text-3xl font-extrabold text-[#0F172A]">₹4,999 <span class="text-xs text-slate-500 font-normal">/ month</span></div>
                        <p class="text-xs text-slate-600">Ideal for single project property agencies & boutique builders.</p>
                        <ul class="space-y-2.5 text-xs text-slate-700 pt-2 font-medium">
                            <li class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-600"></i><span>Up to 5 Staff Users</span></li>
                            <li class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-600"></i><span>2 Active Projects</span></li>
                            <li class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-600"></i><span>500 Leads / Month</span></li>
                            <li class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-600"></i><span>Core CRM & Reports</span></li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="w-full py-3 bg-white hover:bg-slate-100 text-[#0F172A] border border-slate-300 font-extrabold text-xs rounded-xl text-center block transition">Select Starter Plan</a>
                </div>

                <!-- Growth Enterprise Plan -->
                <div class="bg-gradient-to-b from-slate-900 to-slate-950 p-8 rounded-3xl text-white space-y-6 flex flex-col justify-between relative shadow-xl">
                    <div class="absolute -top-3 right-6 bg-[#059669] text-white px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider">Most Popular</div>
                    <div class="space-y-4">
                        <span class="text-xs font-extrabold uppercase text-emerald-400 tracking-wider">Growth Enterprise</span>
                        <div class="text-3xl font-extrabold text-white">₹14,999 <span class="text-xs text-slate-400 font-normal">/ month</span></div>
                        <p class="text-xs text-slate-400">Full operational power for multi-project real estate developers.</p>
                        <ul class="space-y-2.5 text-xs text-slate-300 pt-2 font-medium">
                            <li class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-400"></i><span>Up to 25 Staff Users</span></li>
                            <li class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-400"></i><span>10 Active Projects</span></li>
                            <li class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-400"></i><span>5,000 Leads / Month</span></li>
                            <li class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-400"></i><span>Pessimistic Inventory Lock</span></li>
                            <li class="flex items-center space-x-2"><i class="fa-solid fa-check text-emerald-400"></i><span>Sanitized Broker Portal & Payouts</span></li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="w-full py-3 bg-[#059669] hover:bg-[#047857] text-white font-extrabold text-xs rounded-xl text-center block shadow-lg transition">Get Started Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Banner -->
    <section class="py-16 bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-800 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center space-y-6">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Ready to Elevate Your Real Estate Business?</h2>
            <p class="text-emerald-100 text-sm max-w-xl mx-auto">
                Join modern developers managing multi-tenant inventory, CRM pipelines, and broker channels seamlessly.
            </p>
            <div class="pt-2">
                <a href="{{ route('register') }}" class="px-8 py-4 bg-white text-[#059669] font-extrabold text-sm rounded-2xl shadow-xl hover:bg-emerald-50 transition inline-block">
                    Register Your Company Workspace →
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="space-y-3">
                <div class="flex items-center space-x-2">
                    <span class="text-lg font-extrabold text-white">REOS <span class="text-emerald-400">CRM</span></span>
                </div>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Multi-Tenant Enterprise Operating System for Real Estate Developers, Agencies & Channel Partners.
                </p>
            </div>

            <div>
                <h4 class="font-extrabold text-white uppercase text-[10px] tracking-wider mb-3">Modules</h4>
                <ul class="space-y-2 text-[11px]">
                    <li><a href="#inventory" class="hover:text-white transition">Inventory Locking</a></li>
                    <li><a href="#features" class="hover:text-white transition">Broker Subsystem</a></li>
                    <li><a href="#features" class="hover:text-white transition">KYC Document Vault</a></li>
                    <li><a href="#features" class="hover:text-white transition">Support Ticket Desk</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-extrabold text-white uppercase text-[10px] tracking-wider mb-3">Security & Architecture</h4>
                <ul class="space-y-2 text-[11px]">
                    <li>Laravel 12 REST API Core</li>
                    <li>Sanctum Bearer Token Auth</li>
                    <li>Tenant Scope Multi-Tenancy</li>
                    <li>MySQL Row-Level Locking</li>
                </ul>
            </div>

            <div>
                <h4 class="font-extrabold text-white uppercase text-[10px] tracking-wider mb-3">Workspace Access</h4>
                <div class="space-y-2">
                    <a href="{{ route('login') }}" class="block px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-center font-bold transition">Sign In to Dashboard</a>
                    <a href="{{ route('register') }}" class="block px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-center font-bold transition">Register New Builder</a>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 mt-8 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500">
            <div>© {{ date('Y') }} <strong>REOS Platform</strong>. All rights reserved.</div>
            <div class="mt-2 sm:mt-0 font-mono">Laravel 12.x SaaS Architecture</div>
        </div>
    </footer>

</body>
</html>
