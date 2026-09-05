<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F8FAFC]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'REOS – Real Estate Operating System SaaS')</title>

    <!-- Google Fonts Manrope & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons & TailwindCSS CDN & Alpine.js & Chart.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Manrope', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        navy: '#0F172A',
                        brand: '#059669',
                        reosbg: '#F8FAFC',
                        reoscard: '#FFFFFF',
                        reostext: '#0F172A',
                        reosmuted: '#64748B',
                        reosborder: '#E2E8F0',
                        reosgreen: '#059669',
                        reosamber: '#D97706',
                        reosred: '#DC2626',
                        reosgold: '#C9A227',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            -webkit-font-smoothing: antialiased;
        }

        .glass-nav {
            background: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
        }

        /* Premium Real Estate Operations CRM Card Base */
        .reos-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.03), 0 1px 2px -1px rgba(15, 23, 42, 0.02);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reos-card:hover {
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.06), 0 4px 10px -2px rgba(15, 23, 42, 0.03);
            border-color: #A7F3D0;
        }

        /* Typography Specs */
        .page-heading { font-weight: 700; font-size: 28px; color: #0F172A; letter-spacing: -0.02em; }
        .section-heading { font-weight: 700; font-size: 20px; color: #0F172A; letter-spacing: -0.01em; }
        .kpi-number { font-weight: 700; font-size: 30px; color: #0F172A; font-family: 'JetBrains Mono', monospace; }
        .body-text { font-weight: 400; font-size: 14px; color: #64748B; }
        .table-text { font-weight: 500; font-size: 13px; color: #0F172A; }
        .label-text { font-weight: 600; font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; }
        .btn-text { font-weight: 600; font-size: 14px; }

        /* Standardized Form Controls & Validation Focus States */
        .form-label {
            display: block !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            color: #475569 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            margin-bottom: 0.375rem !important;
        }

        .form-input {
            display: block !important;
            width: 100% !important;
            background-color: #F8FAFC !important;
            border: 1px solid #CBD5E1 !important;
            border-radius: 0.75rem !important;
            padding: 0.625rem 0.875rem !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #0F172A !important;
            box-sizing: border-box !important;
            transition: all 0.15s ease-in-out !important;
        }

        .form-input:focus {
            background-color: #FFFFFF !important;
            border-color: #059669 !important;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15) !important;
            outline: none !important;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="tel"], select, textarea {
            font-family: 'Manrope', sans-serif;
            transition: all 0.15s ease-in-out;
        }
        input:focus, select:focus, textarea:focus {
            outline: none !important;
            border-color: #059669 !important;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15) !important;
        }
        input.is-invalid, select.is-invalid {
            border-color: #DC2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15) !important;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #F8FAFC;
        }
        ::-webkit-scrollbar-thumb {
            background: #A7F3D0;
            border-radius: 9999px;
        }
    </style>
</head>
<body class="h-full flex flex-col font-sans bg-[#F8FAFC] text-[#0F172A] antialiased selection:bg-[#059669] selection:text-white" x-data="{ sidebarOpen: false }">
    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-[#E2E8F0] sticky top-0 z-50 px-4 md:px-6 py-2.5 flex items-center justify-between shadow-2xs">
        <!-- Left: Brand Logo & Company Scope -->
        <div class="flex items-center space-x-4">
            <!-- Mobile Sidebar Toggle Button -->
            <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200 transition">
                <i class="fa-solid fa-bars text-sm"></i>
            </button>

            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group cursor-pointer">
                <div class="w-9 h-9 rounded-xl overflow-hidden shadow-xs border border-emerald-200 bg-white flex items-center justify-center p-0.5">
                    <img src="{{ asset('images/logo.jpg') }}" alt="REOS Logo" class="w-full h-full object-cover rounded-lg">
                </div>
                <div class="flex items-center space-x-2">
                    <span class="font-extrabold text-xl md:text-2xl tracking-tight text-[#0F172A]">REOS <span class="text-[#059669]">CRM</span></span>
                </div>
            </a>

            <!-- Scope / Company Indicator -->
            <div class="hidden md:flex items-center space-x-2 pl-4 border-l border-slate-200">
                @if(auth()->user()->isBroker())
                    <span class="text-xs font-bold text-emerald-900 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200 flex items-center space-x-1.5">
                        <i class="fa-solid fa-globe text-emerald-600"></i>
                        <span>Broker Network</span>
                    </span>
                @elseif(auth()->user()->is_super_admin)
                    <span class="text-xs font-bold text-purple-900 bg-purple-50 px-3 py-1 rounded-lg border border-purple-200 flex items-center space-x-1.5">
                        <i class="fa-solid fa-crown text-purple-600"></i>
                        <span>SaaS Founder Scope</span>
                    </span>
                @else
                    <span class="text-xs font-bold text-[#0F172A] bg-slate-50 px-3 py-1 rounded-lg border border-slate-200 flex items-center space-x-1.5">
                        <i class="fa-solid fa-building text-emerald-600"></i>
                        <span>{{ auth()->user()->company->name ?? 'Enterprise' }}</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Center: Search Keyword Input Bar -->
        <div class="hidden sm:flex items-center flex-1 max-w-md mx-6">
            <div class="relative w-full">
                <input type="text" placeholder="Search Keyword..." class="w-full bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl pl-4 pr-10 py-2 text-xs text-[#0F172A] font-medium placeholder-slate-400 focus:bg-white transition shadow-2xs">
                <div class="absolute right-2.5 top-1.5 px-2 py-0.5 rounded bg-white border border-slate-200 text-[10px] font-mono font-bold text-slate-400">
                    ⌘K
                </div>
            </div>
        </div>

        <!-- Right: Dynamic User Profile & LOGOUT Button -->
        <div class="flex items-center space-x-3">
            <a href="{{ route('notifications.index') }}" class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-900 transition flex items-center justify-center text-xs relative" title="Notifications">
                <i class="fa-regular fa-bell"></i>
                <span class="w-2 h-2 rounded-full bg-[#059669] absolute top-1.5 right-1.5 ring-2 ring-white"></span>
            </a>

            <!-- Dynamic User Profile Info -->
            @php
                $nameParts = explode(' ', auth()->user()->name);
                $initials = count($nameParts) >= 2 
                    ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1))
                    : strtoupper(substr(auth()->user()->name, 0, 2));
            @endphp
            <div class="pl-2 border-l border-slate-200 flex items-center space-x-3">
                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2 group">
                    <div class="w-8 h-8 rounded-full bg-[#047857] text-white font-bold text-xs flex items-center justify-center shadow-2xs group-hover:bg-[#059669] transition">
                        {{ $initials }}
                    </div>
                    <div class="hidden lg:block text-left">
                        <div class="text-xs font-bold text-[#0F172A] leading-tight group-hover:text-[#059669] transition">{{ auth()->user()->name }}</div>
                        @php
                            $headerRoleTitle = match(auth()->user()->role?->slug) {
                                'founder', 'director', 'admin' => 'Admin',
                                'manager', 'sales_manager' => 'Manager',
                                'sales_executive', 'executive' => 'Sales Executive',
                                'broker' => 'Broker',
                                default => (auth()->user()->role->name ?? 'User'),
                            };
                            if (auth()->user()->is_super_admin) {
                                $headerRoleTitle = 'SaaS Founder';
                            }
                        @endphp
                        <div class="text-[10px] font-medium text-slate-400 leading-tight">
                            {{ $headerRoleTitle }}
                        </div>
                    </div>
                </a>

                <!-- DYNAMIC LOGOUT BUTTON FORM -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-[#047857] font-bold text-xs rounded-xl border border-emerald-200 transition flex items-center space-x-1.5 cursor-pointer" title="Sign Out of Session">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        @php
            $u = auth()->user();
            $isBroker = $u->isBroker();
            $isSales = $u->isSales();
            $isManager = $u->isManager();
            $isAdmin = $u->isCompanyAdmin() || $u->isSaaSFounder();
            $isFounder = $u->isSaaSFounder();
        @endphp

        <!-- Clean Off-White Sidebar Navigation -->
        <aside class="w-64 bg-white text-[#0F172A] hidden md:flex flex-col py-5 px-3 space-y-4 border-r border-[#E2E8F0] overflow-y-auto shrink-0">
            
            <!-- CATEGORY 1: CRM & SAAS NAVIGATION -->
            <div class="space-y-1">
                <div class="px-3 pt-1 pb-1 text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">
                    {{ $isFounder ? 'SaaS Platform Founder Scope' : 'CRM Navigation' }}
                </div>

                <!-- 1. Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-[#ECFDF5] text-[#047857] font-bold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857]' }}">
                    <i class="fa-solid fa-house text-xs w-4 text-center {{ request()->routeIs('dashboard') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Dashboard</span>
                </a>

                <!-- 1.1 Team & Broker Chat -->
                <a href="{{ route('chat.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs font-semibold {{ request()->routeIs('chat.*') ? 'bg-[#ECFDF5] text-[#047857] font-bold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857]' }}">
                    <i class="fa-solid fa-comments text-xs w-4 text-center {{ request()->routeIs('chat.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Team Chat</span>
                </a>

                @if($isFounder)
                <!-- 2. Builder Tenant Companies (SuperAdmin) -->
                <a href="{{ route('admin.companies.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('admin.companies.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-building-user text-xs w-4 text-center {{ request()->routeIs('admin.companies.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Builder Companies</span>
                </a>

                <!-- 3. SaaS Subscriptions (SuperAdmin) -->
                <a href="{{ route('admin.saas-subscriptions') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('admin.saas-subscriptions') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-gem text-xs w-4 text-center {{ request()->routeIs('admin.saas-subscriptions') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>SaaS Subscriptions</span>
                </a>
                @endif

                @if(!$isFounder)
                @if(!$isBroker)
                <!-- 1.5 Interactive Calendar Schedule -->
                <a href="{{ route('calendar.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs font-semibold {{ request()->routeIs('calendar.*') ? 'bg-[#ECFDF5] text-[#047857] font-bold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857]' }}">
                    <i class="fa-regular fa-calendar-days text-xs w-4 text-center {{ request()->routeIs('calendar.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Calendar Schedule</span>
                </a>

                <!-- 4. Leads & Sales Pipeline -->
                <a href="{{ route('leads.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('leads.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-chart-line text-xs w-4 text-center {{ request()->routeIs('leads.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                        <span>Leads & Pipeline</span>
                    </div>
                </a>

                <!-- 5. Follow-ups -->
                <a href="{{ route('follow-ups.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('follow-ups.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-phone text-xs w-4 text-center {{ request()->routeIs('follow-ups.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Follow-ups</span>
                </a>

                <!-- 6. Site Visits -->
                <a href="{{ route('site-visits.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('site-visits.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-calendar-check text-xs w-4 text-center {{ request()->routeIs('site-visits.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Site Visits</span>
                </a>

                <!-- 7. Bookings & Contracts -->
                <a href="{{ route('bookings.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('bookings.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-file-contract text-xs w-4 text-center {{ request()->routeIs('bookings.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Bookings & Contracts</span>
                </a>

                <!-- 8. Agreements -->
                <a href="{{ route('agreements.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('agreements.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-signature text-xs w-4 text-center {{ request()->routeIs('agreements.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Agreements</span>
                </a>

                <!-- 9. Payments & Invoices -->
                <a href="{{ route('payments.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('payments.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-receipt text-xs w-4 text-center {{ request()->routeIs('payments.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Payments & Invoices</span>
                </a>

                <!-- 10. Customers -->
                <a href="{{ route('customers.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('customers.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-users text-xs w-4 text-center {{ request()->routeIs('customers.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Customers</span>
                </a>
                @endif
                @endif
            </div>

            @if(!$isFounder)
            <!-- CATEGORY 2: OPERATIONS & INVENTORY -->
            <div class="space-y-1 border-t border-[#E2E8F0] pt-3">
                <div class="px-3 pb-1 text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Operations & Inventory</div>

                <!-- Projects Inventory -->
                <a href="{{ route('projects.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('projects.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-city text-xs w-4 text-center {{ request()->routeIs('projects.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Projects Inventory</span>
                </a>

                @if($isAdmin || $isManager)
                <!-- Brokers -->
                <a href="{{ route('brokers.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('brokers.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-handshake text-xs w-4 text-center {{ request()->routeIs('brokers.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Brokers</span>
                </a>

                <!-- HRMS & Staff Attendance -->
                <a href="{{ route('hrms.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('hrms.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-clipboard-user text-xs w-4 text-center {{ request()->routeIs('hrms.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>HRMS & Attendance</span>
                </a>

                <!-- Team & Users -->
                <a href="{{ route('users.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('users.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-user-tie text-xs w-4 text-center {{ request()->routeIs('users.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Team Users</span>
                </a>

                <!-- Reports & Analytics -->
                <a href="{{ route('reports.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('reports.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-chart-pie text-xs w-4 text-center {{ request()->routeIs('reports.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Reports & Analytics</span>
                </a>
                @endif
            </div>
            @endif

            <!-- CATEGORY 3: SYSTEM VAULT & HELP -->
            <div class="space-y-1 border-t border-[#E2E8F0] pt-3">
                <div class="px-3 pb-1 text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">System Vault & Help</div>

                <!-- Notifications -->
                <a href="{{ route('notifications.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('notifications.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-bell text-xs w-4 text-center {{ request()->routeIs('notifications.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Notifications</span>
                </a>

                @if(!$isFounder)
                <!-- KYC & Document Vault -->
                <a href="{{ route('documents.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('documents.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-folder-open text-xs w-4 text-center {{ request()->routeIs('documents.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>KYC & Document Vault</span>
                </a>
                @endif

                <!-- Support Tickets Helpdesk -->
                <a href="{{ route('support-tickets.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('support-tickets.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-headset text-xs w-4 text-center {{ request()->routeIs('support-tickets.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Support Tickets</span>
                </a>

                @if($isAdmin || $isManager)
                <!-- System Activity Logs -->
                <a href="{{ route('activity-logs.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('activity-logs.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-clock-rotate-left text-xs w-4 text-center {{ request()->routeIs('activity-logs.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Activity Audit Logs</span>
                </a>
                @endif

                @if($isAdmin && !$isFounder)
                <!-- Single Builder Company Settings (RERA License, GSTIN) -->
                <a href="{{ route('company-settings.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('company-settings.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-gear text-xs w-4 text-center {{ request()->routeIs('company-settings.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Company Settings</span>
                </a>
                @endif

                <!-- Account Profile -->
                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl transition text-xs {{ request()->routeIs('profile.*') ? 'bg-[#ECFDF5] text-[#047857] font-extrabold border border-[#A7F3D0]' : 'text-[#475569] hover:bg-emerald-50/50 hover:text-[#047857] font-semibold' }}">
                    <i class="fa-solid fa-user-gear text-xs w-4 text-center {{ request()->routeIs('profile.*') ? 'text-[#059669]' : 'text-slate-400' }}"></i>
                    <span>Account Profile</span>
                </a>
            </div>

            <div class="pt-4 mt-auto border-t border-[#E2E8F0]">
                <div class="p-3 rounded-xl bg-slate-50 border border-[#E2E8F0]">
                    <div class="flex items-center space-x-1.5 text-[10px] font-bold text-emerald-700 uppercase tracking-wider mb-0.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>REOS CRMS Engine</span>
                    </div>
                    <p class="text-[10px] text-slate-500 font-medium">Enterprise SaaS Edition</p>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 md:p-8 pb-24 md:pb-8 bg-[#F8FAFC]">
            <!-- Flash Notification Alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-950 font-bold text-xs flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-800 font-bold">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-950 font-bold text-xs flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-triangle-exclamation text-[#DC2626] text-sm"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-800 font-bold">✕</button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-[#DC2626] font-semibold text-xs space-y-2 shadow-xs">
                    <div class="flex items-center justify-between pb-1.5 border-b border-rose-200/60 font-bold text-sm">
                        <div class="flex items-center space-x-2 text-[#DC2626]">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Please resolve the following validation errors:</span>
                        </div>
                        <button onclick="this.closest('.mb-6').remove()" class="text-rose-400 hover:text-rose-700 font-bold">✕</button>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-slate-700 pl-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Firebase Web SDK & FCM Real-Time Push Notification Engine -->
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js"></script>
    <script>
        const firebaseConfig = {
            apiKey: "{{ config('firebase.api_key') }}",
            authDomain: "{{ config('firebase.auth_domain') }}",
            databaseURL: "{{ config('firebase.database_url') }}",
            projectId: "{{ config('firebase.project_id') }}",
            storageBucket: "{{ config('firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('firebase.messaging_sender_id') }}",
            appId: "{{ config('firebase.app_id') }}",
            measurementId: "{{ config('firebase.measurement_id') }}"
        };

        if (typeof firebase !== 'undefined') {
            firebase.initializeApp(firebaseConfig);

            if ('serviceWorker' in navigator && firebase.messaging.isSupported()) {
                const messaging = firebase.messaging();

                // Register Background Service Worker
                navigator.serviceWorker.register('/firebase-messaging-sw.js')
                    .then((registration) => {
                        messaging.useServiceWorker(registration);

                        // Request Notification Permission
                        Notification.requestPermission().then((permission) => {
                            if (permission === 'granted') {
                                messaging.getToken().then((currentToken) => {
                                    if (currentToken) {
                                        // Auto-register FCM device token to user account via API
                                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                        fetch('/api/v1/fcm-token', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': csrfToken
                                            },
                                            body: JSON.stringify({ fcm_token: currentToken })
                                        }).catch(err => console.log('FCM token registration background sync silent pass'));
                                    }
                                });
                            }
                        });
                    }).catch(err => console.log('SW registration error:', err));

                // Foreground Notification Toast
                messaging.onMessage((payload) => {
                    console.log('Foreground FCM Push Received: ', payload);
                    const title = payload.notification ? payload.notification.title : (payload.data ? payload.data.title : 'REOS Alert');
                    const body = payload.notification ? payload.notification.body : (payload.data ? payload.data.body : '');

                    // Display sleek toast notification
                    const toast = document.createElement('div');
                    toast.className = 'fixed bottom-5 right-5 z-50 p-4 rounded-2xl bg-slate-900 text-white shadow-2xl border border-slate-700 flex items-center space-x-3 max-w-sm transition transform translate-y-0 opacity-100';
                    toast.innerHTML = `
                        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold truncate">${title}</h4>
                            <p class="text-[11px] text-slate-300 truncate">${body}</p>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white text-xs font-bold ml-2">✕</button>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => { if (toast) toast.remove(); }, 6000);
                });
            }
        }
    </script>
</body>
</html>
