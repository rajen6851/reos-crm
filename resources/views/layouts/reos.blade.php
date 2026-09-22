<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F8FAFC]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'UrbanProperty â€“ Real Estate Operating System SaaS')</title>

    <!-- Google Fonts Plus Jakarta Sans, Manrope & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome Icons & TailwindCSS CDN & Alpine.js & Chart.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Manrope', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
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
            font-family: 'Plus Jakarta Sans', 'Manrope', -apple-system, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
        }

        .reos-card {
            background-color: #FFFFFF;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 0.75rem;
            /* 12px / rounded-xl */
            box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.03), 0 1px 2px -1px rgba(15, 23, 42, 0.02);
        }

        .reos-stat-card {
            background-color: #FFFFFF;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.03);
            transition: all 0.2s ease-in-out;
        }

        .reos-stat-card:hover {
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.05), 0 2px 4px -2px rgba(15, 23, 42, 0.03);
            border-color: rgba(203, 213, 225, 0.9);
        }

        .page-heading {
            font-size: 1.375rem;
            /* 22px */
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0F172A;
        }

        .body-text {
            font-size: 0.75rem;
            color: #64748B;
            font-weight: 500;
        }

        .reos-table-header {
            background-color: #F8FAFC;
            color: #64748B;
            font-size: 0.6875rem;
            /* 11px */
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #E2E8F0;
        }
    </style>

    <!-- UrbanProperty CRM Global Frontend Console Logger & Interceptors -->
    <script>
        window.ReosLogger = {
            prefix: '[UrbanProperty CRM]',
            info: function (msg, data = '') {
                console.log(`%c${this.prefix} [INFO] â„¹ï¸ ${msg}`, 'color: #0284C7; font-weight: 700; background: #E0F2FE; padding: 2px 6px; border-radius: 4px;', data);
            },
            success: function (msg, data = '') {
                console.log(`%c${this.prefix} [SUCCESS] âœ… ${msg}`, 'color: #059669; font-weight: 700; background: #D1FAE5; padding: 2px 6px; border-radius: 4px;', data);
            },
            warn: function (msg, data = '') {
                console.warn(`%c${this.prefix} [WARN] âš ï¸ ${msg}`, 'color: #D97706; font-weight: 700; background: #FEF3C7; padding: 2px 6px; border-radius: 4px;', data);
            },
            error: function (msg, err = '') {
                console.error(`%c${this.prefix} [ERROR] âŒ ${msg}`, 'color: #DC2626; font-weight: 700; background: #FEE2E2; padding: 2px 6px; border-radius: 4px;', err);
            },
            ajax: function (method, url, status, duration, data = '') {
                const color = status >= 200 && status < 300 ? '#059669' : '#DC2626';
                console.log(`%c${this.prefix} [AJAX] ðŸŒ ${method} ${url} â†’ ${status} (${duration}ms)`, `color: ${color}; font-weight: 600;`, data);
            }
        };

        // System Startup Log
        window.ReosLogger.info('Browser Console Logging Engine Online', { app: 'UrbanProperty SaaS CRM', timestamp: new Date().toISOString() });

        // Global Fetch Interceptor for AJAX Logging
        (function () {
            const originalFetch = window.fetch;
            window.fetch = async function (...args) {
                const startTime = performance.now();
                const url = typeof args[0] === 'string' ? args[0] : (args[0]?.url || 'URL');
                const method = args[1]?.method || 'GET';

                try {
                    const response = await originalFetch.apply(this, args);
                    const duration = Math.round(performance.now() - startTime);
                    window.ReosLogger.ajax(method, url, response.status, duration);
                    return response;
                } catch (error) {
                    const duration = Math.round(performance.now() - startTime);
                    window.ReosLogger.error(`FETCH FAILED: ${method} ${url} (${duration}ms)`, error);
                    throw error;
                }
            };
        })();

        // Global Window Error Listener
        window.addEventListener('error', function (event) {
            window.ReosLogger.error(`Uncaught Error: ${event.message} at ${event.filename}:${event.lineno}`, event.error);
        });

        window.addEventListener('unhandledrejection', function (event) {
            window.ReosLogger.error(`Unhandled Promise Rejection: ${event.reason}`, event.reason);
        });

        // Form Submit Listener
        document.addEventListener('submit', function (event) {
            const form = event.target;
            const formId = form.id ? `#${form.id}` : (form.name ? `[name="${form.name}"]` : 'form');
            const action = form.action || window.location.href;
            window.ReosLogger.info(`Form Submitted: ${formId} â†’ ${action}`);
        }, true);
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

        /* Enterprise CRM Card Base */
        .reos-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.04);
            transition: all 0.15s ease-in-out;
        }

        .reos-card:hover {
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.06);
            border-color: #CBD5E1;
        }

        /* Typography Specs */
        .page-heading {
            font-weight: 700;
            font-size: 28px;
            color: #0F172A;
            letter-spacing: -0.02em;
        }

        .section-heading {
            font-weight: 700;
            font-size: 20px;
            color: #0F172A;
            letter-spacing: -0.01em;
        }

        .kpi-number {
            font-weight: 700;
            font-size: 30px;
            color: #0F172A;
            font-family: 'JetBrains Mono', monospace;
        }

        .body-text {
            font-weight: 400;
            font-size: 14px;
            color: #64748B;
        }

        .table-text {
            font-weight: 500;
            font-size: 13px;
            color: #0F172A;
        }

        .label-text {
            font-weight: 600;
            font-size: 12px;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-text {
            font-weight: 600;
            font-size: 14px;
        }

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

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="tel"],
        select,
        textarea {
            font-family: 'Manrope', sans-serif;
            transition: all 0.15s ease-in-out;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none !important;
            border-color: #059669 !important;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15) !important;
        }

        input.is-invalid,
        select.is-invalid {
            border-color: #DC2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15) !important;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #111936;
        }

        ::-webkit-scrollbar-thumb {
            background: #253154;
            border-radius: 9999px;
        }
    </style>
</head>

<body
    class="h-full flex flex-col font-sans bg-[#F8FAFC] text-[#0F172A] antialiased selection:bg-[#2563EB] selection:text-white"
    x-data="{ 
        sidebarOpen: false, 
        sidebarCollapsed: localStorage.getItem('reos_sidebar_collapsed') === 'true',
        toggleSidebarCollapsed() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('reos_sidebar_collapsed', this.sidebarCollapsed);
        },
        activeCat: {
            sales: true,
            ops: true,
            system: true,
            saas: true
        },
        toggleCat(cat) {
            this.activeCat[cat] = !this.activeCat[cat];
        }
    }">

    <!-- Enterprise Top Navigation Bar -->
    <header
        class="bg-white border-b border-slate-200 sticky top-0 z-50 px-4 md:px-6 h-14 flex items-center justify-between shadow-2xs select-none">
        <!-- Left: Mobile Toggle & Topbar Breadcrumb Path -->
        <div class="flex items-center space-x-3">
            <!-- Mobile Sidebar Toggle Button -->
            <button @click="sidebarOpen = !sidebarOpen"
                class="md:hidden p-1.5 text-slate-600 hover:bg-slate-100 rounded border border-slate-200 transition">
                <i class="fa-solid fa-bars text-sm"></i>
            </button>

            <!-- Desktop Mini Sidebar Collapse Button -->
            <button @click="toggleSidebarCollapsed()"
                class="hidden md:flex items-center justify-center p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded border border-slate-200 transition shadow-2xs"
                :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
                <i class="fa-solid text-xs" :class="sidebarCollapsed ? 'fa-indent' : 'fa-outdent'"></i>
            </button>

            <!-- Breadcrumbs in Topbar (Matching Screenshot) -->
            <nav class="hidden sm:flex items-center space-x-1.5 text-xs text-slate-500 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800 transition">Home</a>
                <span class="text-slate-300">&gt;</span>
                <span>Central Operations</span>
                <span class="text-slate-300">&gt;</span>
                <span class="text-slate-900 font-bold">@yield('title', 'Dashboard')</span>
            </nav>
        </div>

        <!-- Center: Enterprise Global Search Input Bar (Matching Screenshot Ctrl+K) -->
        <div class="flex items-center flex-1 max-w-xl mx-4 sm:mx-8">
            <div class="relative w-full">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-2.5 text-xs text-slate-400"></i>
                <input type="text" placeholder="Search leads, units, agents (Ctrl+K)..."
                    class="w-full bg-[#F8FAFC] border border-slate-200 rounded-lg pl-9 pr-16 py-1.5 text-xs text-[#0F172A] font-medium placeholder-slate-400 focus:bg-white focus:border-[#2563EB] transition shadow-2xs">
                <div
                    class="absolute right-2.5 top-1.5 px-1.5 py-0.5 rounded bg-white border border-slate-200 text-[10px] font-mono font-bold text-slate-400 select-none">
                    Ctrl+K
                </div>
            </div>
        </div>

        <!-- Right: Notification Bell & Royal Blue + Quick Add Button -->
        <div class="flex items-center space-x-3">
            <!-- Notifications Bell -->
            <a href="{{ route('notifications.index') }}"
                class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-600 transition flex items-center justify-center text-sm relative"
                title="Notifications">
                <i class="fa-regular fa-bell"></i>
                <span
                    class="w-4 h-4 rounded-full bg-[#EF4444] text-white text-[9px] font-bold flex items-center justify-center absolute -top-0.5 -right-0.5 ring-2 ring-white">1</span>
            </a>

            @php
                $isPlatformOnlyHeaderUser = auth()->user()->isSaaSFounder() || auth()->user()->isSaaSSubAdmin();
            @endphp

            <!-- + Quick Add â–¾ Button (Royal Blue with Chevron) -->
            @if(!$isPlatformOnlyHeaderUser)
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false"
                        class="px-4 py-1.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-lg transition flex items-center space-x-1.5 shadow-xs cursor-pointer">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Quick Add</span>
                        <i class="fa-solid fa-chevron-down text-[9px] ml-0.5"></i>
                    </button>

                    <div x-show="open" x-transition
                        class="absolute right-0 mt-1.5 w-48 bg-white rounded-lg border border-slate-200 shadow-lg py-1 z-50 text-xs">
                        <a href="{{ route('leads.index') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-slate-700 hover:bg-slate-50 font-semibold">
                            <i class="fa-solid fa-user-plus text-emerald-600 text-xs w-4"></i>
                            <span>Create New Lead</span>
                        </a>
                        <a href="{{ route('site-visits.index') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-slate-700 hover:bg-slate-50 font-semibold">
                            <i class="fa-solid fa-calendar-check text-amber-600 text-xs w-4"></i>
                            <span>Schedule Site Visit</span>
                        </a>
                        <a href="{{ route('bookings.index') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-slate-700 hover:bg-slate-50 font-semibold">
                            <i class="fa-solid fa-file-contract text-indigo-600 text-xs w-4"></i>
                            <span>New Unit Booking</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        @php
            $u = auth()->user();
            $isBroker = $u->isBroker();
            $isSales = $u->isSales();
            $isManager = $u->isManager();
            $isAdmin = $u->isCompanySubAdmin();
            $isDirector = $u->isDirector();
            $isCompanyFounder = $u->isCompanyFounder();
            $isCompanyLeadership = $isDirector || $isCompanyFounder;
            $isFounder = $u->isSaaSFounder();
            $isSubAdmin = $u->isSaaSSubAdmin();
            $isSaasAdmin = $u->isSaaSAdmin(); // true for both founder + sub-admin
            // SaaS identity takes precedence over any legacy company role stored on the account.
            $isSaasPlatformOnly = $isFounder || $isSubAdmin;
            $pendingApprovalsCount = $isSaasAdmin ? \App\Models\SaasApprovalRequest::where('status', 'pending')->count() : 0;
            $companyPendingApprovalsCount = ($u->isDirectorOrFounder() && $u->company_id)
                ? \App\Models\SaasApprovalRequest::where('company_id', $u->company_id)->where('status', 'pending')->count()
                : 0;
        @endphp

        <!-- Enterprise Dark Navy Sidebar Navigation (#111936 / #0F172A) -->
        <aside :class="sidebarCollapsed ? 'w-16 px-1.5' : 'w-60 px-3'"
            class="bg-[#111936] text-white hidden md:flex flex-col py-4 space-y-2 border-r border-[#1E294A] overflow-y-auto shrink-0 transition-all duration-200 ease-in-out select-none">

            <!-- Brand Logo at top of sidebar (Matching Screenshot) -->
            <div class="px-2 pb-3 mb-2 border-b border-[#1E294A] flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-blue-600 p-0.5 shrink-0 flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-house-chimney text-white text-sm"></i>
                </div>
                <div x-show="!sidebarCollapsed" class="flex items-center space-x-1.5 truncate">
                    <span class="font-extrabold text-lg tracking-tight text-white">UrbanProperty <span
                            class="text-blue-400">CRM</span></span>
                </div>
            </div>

            <!-- Navigation Links List (Organized Categorized Sections with Role Access Control) -->
            <nav class="space-y-4">

                <!-- SECTION 1: OVERVIEW -->
                <div class="space-y-1">
                    <div x-show="!sidebarCollapsed"
                        class="px-3 pt-1 pb-1 text-[10px] font-extrabold text-[#38BDF8] tracking-wider uppercase select-none">
                        Overview
                    </div>

                    <!-- Dashboard (All Roles) -->
                    <a href="{{ route('dashboard') }}" :title="sidebarCollapsed ? 'Dashboard' : ''"
                        class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                        <i
                            class="fa-solid fa-table-cells-large text-sm w-4 text-center {{ request()->routeIs('dashboard') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
                    </a>
                </div>

                <!-- SECTION 2: SALES & PIPELINE -->
                {{-- Brokers: show only their own referral-related items (Removed redundant links as broker portal is a
                single-page dashboard) --}}


                {{-- Internal Staff: Full Sales & Pipeline section --}}
                @if(!$isBroker && !$isSaasPlatformOnly)
                    <div class="space-y-1">
                        <div x-show="!sidebarCollapsed"
                            class="px-3 pt-2 pb-1 text-[10px] font-extrabold text-[#38BDF8] tracking-wider uppercase select-none border-t border-[#1E294A]/60">
                            Sales & Pipeline
                        </div>
                        <div x-show="sidebarCollapsed" class="border-t border-[#1E294A]/60 my-1"></div>

                        <!-- Leads (All Internal) -->
                        <a href="{{ route('leads.index') }}" :title="sidebarCollapsed ? 'Leads' : ''"
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('leads.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                            <i
                                class="fa-solid fa-user-plus text-sm w-4 text-center {{ request()->routeIs('leads.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Leads</span>
                        </a>

                        <!-- Contacts (Requires manage-leads permission) -->
                        @if($u->hasPermission('manage-leads'))
                            <a href="{{ route('customers.index') }}" :title="sidebarCollapsed ? 'Contacts' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('customers.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-regular fa-address-book text-sm w-4 text-center {{ request()->routeIs('customers.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Contacts</span>
                            </a>
                        @endif

                        <!-- Properties (All Internal) -->
                        <a href="{{ route('projects.index') }}" :title="sidebarCollapsed ? 'Properties' : ''"
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('projects.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                            <i
                                class="fa-solid fa-building text-sm w-4 text-center {{ request()->routeIs('projects.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Properties</span>
                        </a>

                        <!-- Site Visits (All Internal) -->
                        <a href="{{ route('site-visits.index') }}" :title="sidebarCollapsed ? 'Site Visits' : ''"
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('site-visits.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                            <i
                                class="fa-solid fa-location-dot text-sm w-4 text-center {{ request()->routeIs('site-visits.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Site Visits</span>
                        </a>

                        <!-- Deals / Bookings (Requires approve-bookings) -->
                        @if($u->hasPermission('approve-bookings') || $isSaasAdmin)
                            <a href="{{ route('bookings.index') }}" :title="sidebarCollapsed ? 'Deals' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('bookings.*', 'agreements.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-sack-dollar text-sm w-4 text-center {{ request()->routeIs('bookings.*', 'agreements.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Deals</span>
                            </a>
                        @endif
                    </div>
                @endif

                <!-- SECTION 3: OPERATIONS (Internal Staff Only â€” not Broker, not SaaS-only admins) -->
                @if(!$isBroker && !$isSaasPlatformOnly && !($isSaasAdmin && !$isAdmin && !$isDirector && !$isManager && !$isSales))
                    <div class="space-y-1">
                        <div x-show="!sidebarCollapsed"
                            class="px-3 pt-2 pb-1 text-[10px] font-extrabold text-[#38BDF8] tracking-wider uppercase select-none border-t border-[#1E294A]/60">
                            Operations
                        </div>
                        <div x-show="sidebarCollapsed" class="border-t border-[#1E294A]/60 my-1"></div>

                        <!-- Tasks / Calendar (All internal staff) -->
                        <a href="{{ route('calendar.index') }}" :title="sidebarCollapsed ? 'Tasks' : ''"
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('calendar.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                            <i
                                class="fa-solid fa-list-check text-sm w-4 text-center {{ request()->routeIs('calendar.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Tasks</span>
                        </a>

                        <!-- Follow-ups (All internal staff) -->
                        <a href="{{ route('follow-ups.index') }}" :title="sidebarCollapsed ? 'Follow-ups' : ''"
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('follow-ups.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                            <i
                                class="fa-solid fa-rocket text-sm w-4 text-center {{ request()->routeIs('follow-ups.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Follow-ups</span>
                        </a>

                        <!-- Documents (Requires Company Admin or Founder) -->
                        @if($u->isCompanyAdmin() || $u->isCompanyFounder() || $u->isSaaSFounder())
                            <a href="{{ route('documents.index') }}" :title="sidebarCollapsed ? 'Documents' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('documents.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-regular fa-file-lines text-sm w-4 text-center {{ request()->routeIs('documents.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Documents</span>
                            </a>
                        @endif

                        <!-- HRMS (All Internal Staff) -->
                        @if(Route::has('hrms.index') && !$isBroker)
                            <a href="{{ route('hrms.index') }}" :title="sidebarCollapsed ? 'HRMS' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('hrms.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-user-clock text-sm w-4 text-center {{ request()->routeIs('hrms.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">HRMS</span>
                            </a>
                        @endif

                        <!-- Support Desk (All internal staff) -->
                        @if(Route::has('support-tickets.index'))
                            <a href="{{ route('support-tickets.index') }}" :title="sidebarCollapsed ? 'Support Desk' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('support-tickets.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-headset text-sm w-4 text-center {{ request()->routeIs('support-tickets.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Support Desk</span>
                            </a>
                        @endif

                        <!-- Team Chat (All internal staff) -->
                        <!-- @if(Route::has('chat.index'))
                                <a href="{{ route('chat.index') }}" :title="sidebarCollapsed ? 'Team Chat' : ''"
                                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('chat.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                    <i class="fa-solid fa-comments text-sm w-4 text-center {{ request()->routeIs('chat.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                    <span x-show="!sidebarCollapsed" class="truncate">Team Chat</span>
                                </a>
                                @endif -->
                    </div>
                @endif

                <!-- SECTION 4: MANAGEMENT -->
                @if(!$isSaasPlatformOnly && ($u->hasPermission('manage-users') || $u->hasPermission('manage-commissions') || $u->hasPermission('view-reports') || $u->hasPermission('company-settings') || $companyPendingApprovalsCount > 0))
                    <div class="space-y-1">
                        <div x-show="!sidebarCollapsed"
                            class="px-3 pt-2 pb-1 text-[10px] font-extrabold text-[#38BDF8] tracking-wider uppercase select-none border-t border-[#1E294A]/60">
                            Management
                        </div>
                        <div x-show="sidebarCollapsed" class="border-t border-[#1E294A]/60 my-1"></div>

                        <!-- Teams -->
                        @if($u->hasPermission('manage-users'))
                            <a href="{{ route('users.index') }}" :title="sidebarCollapsed ? 'Teams' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('users.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-users-gear text-sm w-4 text-center {{ request()->routeIs('users.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Teams</span>
                            </a>
                        @endif

                        <!-- Brokers Directory -->
                        @if($u->hasPermission('manage-commissions'))
                            <a href="{{ route('brokers.index') }}" :title="sidebarCollapsed ? 'Brokers' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('brokers.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-handshake text-sm w-4 text-center {{ request()->routeIs('brokers.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Brokers</span>
                            </a>
                        @endif

                        <!-- Reports -->
                        @if($u->hasPermission('view-reports'))
                            <a href="{{ route('reports.index') }}" :title="sidebarCollapsed ? 'Reports' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('reports.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-chart-column text-sm w-4 text-center {{ request()->routeIs('reports.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Reports</span>
                            </a>
                        @endif

                        <!-- Activity Log -->
                        @if($u->can('view-activity-logs'))
                            <a href="{{ route('activity-logs.index') }}" :title="sidebarCollapsed ? 'Activity Log' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('activity-logs.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-list-check text-sm w-4 text-center {{ request()->routeIs('activity-logs.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Activity Log</span>
                            </a>
                        @endif

                        <!-- Push Notifications -->
                        @if(Route::has('notifications.index') && ($isCompanyFounder || $isDirector || $isAdmin))
                            <a href="{{ route('notifications.index') }}" :title="sidebarCollapsed ? 'Notifications' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('notifications.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-bullhorn text-sm w-4 text-center {{ request()->routeIs('notifications.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Push Notifications</span>
                            </a>
                        @endif

                        <!-- Payments -->
                        @if($u->isCompanyAdmin() || $u->role?->slug === 'founder' || $u->isManager() || $u->hasPermission('process-payouts') || $u->hasPermission('manage-commissions') || $u->hasPermission('view-financials'))
                            <a href="{{ route('payments.index') }}" :title="sidebarCollapsed ? 'Payments' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('payments.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-credit-card text-sm w-4 text-center {{ request()->routeIs('payments.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Payments</span>
                            </a>
                        @endif

                        <!-- Settings & Lead Sources -->
                        @if($u->hasPermission('company-settings'))
                            <a href="{{ route('company-settings.index') }}" :title="sidebarCollapsed ? 'Settings' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('company-settings.*', 'profile.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-gear text-sm w-4 text-center {{ request()->routeIs('company-settings.*', 'profile.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Settings</span>
                            </a>
                            <!-- <a href="{{ route('lead-sources.index') }}" :title="sidebarCollapsed ? 'Lead Sources' : ''"
                                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('lead-sources.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                                <i
                                                    class="fa-solid fa-plug text-sm w-4 text-center {{ request()->routeIs('lead-sources.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                                <span x-show="!sidebarCollapsed" class="truncate">Lead Sources</span>
                                            </a> -->
                        @endif

                        <!-- Lead Distribution -->
                        @if($isCompanyFounder || $isDirector || $isAdmin)
                            <a href="{{ route('distribution-rules.index') }}"
                                :title="sidebarCollapsed ? 'Lead Distribution' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('distribution-rules.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-sitemap text-sm w-4 text-center {{ request()->routeIs('distribution-rules.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Lead Distribution</span>
                            </a>
                        @endif

                        <!-- Permissions Matrix (Director ONLY) -->
                        @if($isCompanyLeadership)
                            <a href="{{ route('permissions.index') }}" :title="sidebarCollapsed ? 'Permissions' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('permissions.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-shield-halved text-sm w-4 text-center {{ request()->routeIs('permissions.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Permissions</span>
                            </a>
                        @endif

                        <!-- Company Approvals Badge (Director, Admin) -->
                        @if($companyPendingApprovalsCount > 0 && ($isAdmin || $isCompanyLeadership))
                            <a href="{{ route('company.approvals') }}" :title="sidebarCollapsed ? 'Company Approvals' : ''"
                                class="flex items-center justify-between px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('company.approvals') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <div class="flex items-center space-x-3 truncate">
                                    <i class="fa-solid fa-shield-cat text-sm w-4 text-center text-rose-400"></i>
                                    <span x-show="!sidebarCollapsed" class="truncate">Action Approvals</span>
                                </div>
                                <span
                                    class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-rose-600 text-white">{{ $companyPendingApprovalsCount }}</span>
                            </a>
                        @endif
                    </div>
                @endif

                <!-- SECTION 5: SAAS FOUNDER CONTROL TOWER (Full SaaS Control) -->
                @if($isFounder)
                    <div class="space-y-1">
                        <div x-show="!sidebarCollapsed"
                            class="px-3 pt-2 pb-1 text-[10px] font-extrabold text-[#38BDF8] tracking-wider uppercase select-none border-t border-[#1E294A]/60">
                            SaaS Control Tower
                        </div>
                        <div x-show="sidebarCollapsed" class="border-t border-[#1E294A]/60 my-1"></div>

                        <!-- Tenant Companies -->
                        <a href="{{ route('admin.companies.index') }}" :title="sidebarCollapsed ? 'Tenant Companies' : ''"
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('admin.companies.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                            <i
                                class="fa-solid fa-city text-sm w-4 text-center {{ request()->routeIs('admin.companies.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Tenant Companies</span>
                        </a>

                        <!-- SaaS Plans (Founder full management) -->
                        <a href="{{ route('admin.saas-subscriptions') }}" :title="sidebarCollapsed ? 'SaaS Plans' : ''"
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('admin.saas-subscriptions') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                            <i
                                class="fa-solid fa-bolt text-sm w-4 text-center {{ request()->routeIs('admin.saas-subscriptions') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">SaaS Plans</span>
                        </a>

                        <!-- Sub-Admins Management (Founder ONLY) -->
                        <a href="{{ route('admin.sub-admins.index') }}" :title="sidebarCollapsed ? 'Sub-Admins' : ''"
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('admin.sub-admins.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                            <i
                                class="fa-solid fa-user-shield text-sm w-4 text-center {{ request()->routeIs('admin.sub-admins.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Sub-Admins</span>
                        </a>

                        <!-- Platform Permissions Matrix -->
                        <a href="{{ route('permissions.index') }}" :title="sidebarCollapsed ? 'Permissions' : ''"
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('permissions.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                            <i
                                class="fa-solid fa-shield-halved text-sm w-4 text-center {{ request()->routeIs('permissions.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Permissions</span>
                        </a>

                        <!-- Audit Logs -->
                        @if(Route::has('admin.audit-logs'))
                            <a href="{{ route('admin.audit-logs') }}" :title="sidebarCollapsed ? 'Audit Logs' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('admin.audit-logs') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-scroll text-sm w-4 text-center {{ request()->routeIs('admin.audit-logs') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Audit Logs</span>
                            </a>
                        @endif

                        <!-- SaaS Approvals Badge -->
                        @if($pendingApprovalsCount > 0)
                            <a href="{{ route('admin.saas-approvals') }}" :title="sidebarCollapsed ? 'SaaS Approvals' : ''"
                                class="flex items-center justify-between px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('admin.saas-approvals') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <div class="flex items-center space-x-3 truncate">
                                    <i class="fa-solid fa-bell text-sm w-4 text-center text-amber-400"></i>
                                    <span x-show="!sidebarCollapsed" class="truncate">SaaS Approvals</span>
                                </div>
                                <span
                                    class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500 text-white">{{ $pendingApprovalsCount }}</span>
                            </a>
                        @endif
                    </div>
                @endif

                <!-- SECTION 5b: SAAS SUB-ADMIN DELEGATED PANEL -->
                @if($isSubAdmin)
                    <div class="space-y-1">
                        <div x-show="!sidebarCollapsed"
                            class="px-3 pt-2 pb-1 text-[10px] font-extrabold text-[#38BDF8] tracking-wider uppercase select-none border-t border-[#1E294A]/60">
                            SaaS Delegate Panel
                        </div>
                        <div x-show="sidebarCollapsed" class="border-t border-[#1E294A]/60 my-1"></div>

                        <!-- Tenant Companies (if permitted) -->
                        @if(auth()->user()->hasSaaSPermission('view_companies'))
                            <a href="{{ route('admin.companies.index') }}" :title="sidebarCollapsed ? 'Tenant Companies' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('admin.companies.*') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-city text-sm w-4 text-center {{ request()->routeIs('admin.companies.*') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">Tenant Companies</span>
                            </a>
                        @endif

                        <!-- SaaS Plans (permission-controlled; changes by Sub-Admin go through approval) -->
                        @if(auth()->user()->hasSaaSPermission('manage_subscriptions'))
                            <a href="{{ route('admin.saas-subscriptions') }}" :title="sidebarCollapsed ? 'SaaS Plans' : ''"
                                class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('admin.saas-subscriptions') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                                <i
                                    class="fa-solid fa-bolt text-sm w-4 text-center {{ request()->routeIs('admin.saas-subscriptions') ? 'text-white' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">SaaS Plans</span>
                            </a>
                        @endif


                        <!-- SaaS Approvals Badge (Sub-Admin can view) -->
                        <a href="{{ route('admin.saas-approvals') }}" :title="sidebarCollapsed ? 'SaaS Approvals' : ''"
                            class="flex items-center justify-between px-3 py-2.5 rounded-lg transition text-xs font-semibold {{ request()->routeIs('admin.saas-approvals') ? 'bg-[#253154] text-white font-bold shadow-xs' : 'text-[#94A3B8] hover:bg-[#1E294A] hover:text-white' }}">
                            <div class="flex items-center space-x-3 truncate">
                                <i
                                    class="fa-solid fa-bell text-sm w-4 text-center {{ $pendingApprovalsCount > 0 ? 'text-amber-400' : 'text-[#94A3B8]' }}"></i>
                                <span x-show="!sidebarCollapsed" class="truncate">SaaS Approvals</span>
                            </div>
                            @if($pendingApprovalsCount > 0)
                                <span
                                    class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500 text-white">{{ $pendingApprovalsCount }}</span>
                            @endif
                        </a>
                    </div>
                @endif
            </nav>

            <!-- Bottom User Profile & Logout Section -->
            <div class="pt-4 mt-auto border-t border-[#1E294A] space-y-2">
                @php
                    $nameParts = explode(' ', auth()->user()->name);
                    $initials = count($nameParts) >= 2
                        ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1))
                        : strtoupper(substr(auth()->user()->name, 0, 2));
                @endphp

                {{-- Clickable Profile Card â†’ goes to Profile Edit page --}}
                <a href="{{ route('profile.edit') }}" title="Edit My Profile"
                    class="group flex items-center space-x-3 px-2 py-2 rounded-lg hover:bg-[#1E294A] transition cursor-pointer">
                    <div class="relative shrink-0">
                        <div
                            class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center border border-blue-400 group-hover:ring-2 group-hover:ring-blue-400 transition">
                            {{ $initials }}
                        </div>
                        {{-- Edit pencil badge on hover --}}
                        <span
                            class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-[#1E294A] border border-[#2D3F6B] rounded-full items-center justify-center hidden group-hover:flex transition">
                            <i class="fa-solid fa-pen text-[6px] text-blue-400"></i>
                        </span>
                    </div>
                    <div x-show="!sidebarCollapsed" class="truncate flex-1 min-w-0">
                        <div class="text-xs font-bold text-white truncate group-hover:text-blue-300 transition">
                            {{ auth()->user()->name }}
                        </div>
                        <div
                            class="text-[10px] text-[#94A3B8] capitalize truncate group-hover:text-blue-400 transition">
                            {{ auth()->user()->role?->name ?? 'Admin User' }}
                        </div>
                    </div>
                    <i x-show="!sidebarCollapsed"
                        class="fa-solid fa-pen-to-square text-[10px] text-[#94A3B8] group-hover:text-blue-400 transition shrink-0"></i>
                </a>

                <a href="{{ route('my-permissions') }}" title="My Permissions"
                    class="flex items-center space-x-3 px-3 py-2 rounded-lg text-[#94A3B8] hover:text-indigo-400 hover:bg-[#1E294A] text-xs font-semibold transition cursor-pointer">
                    <i class="fa-solid fa-shield-halved text-sm w-4 text-center"></i>
                    <span x-show="!sidebarCollapsed">My Permissions</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-[#94A3B8] hover:text-rose-400 hover:bg-[#1E294A] text-xs font-semibold transition cursor-pointer">
                        <i class="fa-solid fa-arrow-right-from-bracket text-sm w-4 text-center"></i>
                        <span x-show="!sidebarCollapsed">Logout</span>
                    </button>
                </form>
            </div>

        </aside>


        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 md:p-8 pb-24 md:pb-8 bg-[#F8FAFC]">
            <!-- Flash Notification Alerts -->
            @if(session('success'))
                <div
                    class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-950 font-bold text-xs flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-800 font-bold">âœ•</button>
                </div>
            @endif

            @if(session('error'))
                <div
                    class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-950 font-bold text-xs flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-triangle-exclamation text-[#DC2626] text-sm"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-800 font-bold">âœ•</button>
                </div>
            @endif

            @if(session('warning'))
                <div
                    class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-950 font-bold text-xs flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-amber-800 font-bold">âœ•</button>
                </div>
            @endif

            @if($errors->any())
                <div
                    class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-[#DC2626] font-semibold text-xs space-y-2 shadow-xs">
                    <div class="flex items-center justify-between pb-1.5 border-b border-rose-200/60 font-bold text-sm">
                        <div class="flex items-center space-x-2 text-[#DC2626]">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Please resolve the following validation errors:</span>
                        </div>
                        <button onclick="this.closest('.mb-6').remove()"
                            class="text-rose-400 hover:text-rose-700 font-bold">âœ•</button>
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
            window.ReosLogger.success('Firebase SDK Initialized', { projectId: firebaseConfig.projectId });

            if ('serviceWorker' in navigator && firebase.messaging.isSupported()) {
                const messaging = firebase.messaging();

                // Register Background Service Worker
                navigator.serviceWorker.register('/firebase-messaging-sw.js')
                    .then((registration) => {
                        messaging.useServiceWorker(registration);
                        window.ReosLogger.info('Service Worker Registered for FCM', { scope: registration.scope });

                        // Request Notification Permission
                        Notification.requestPermission().then((permission) => {
                            window.ReosLogger.info(`Notification Permission: ${permission}`);

                            if (permission === 'granted') {
                                messaging.getToken().then((currentToken) => {
                                    if (currentToken) {
                                        window.ReosLogger.success('FCM Device Token Acquired', currentToken);
                                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                        fetch('/fcm-token', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': csrfToken
                                            },
                                            body: JSON.stringify({ fcm_token: currentToken })
                                        })
                                            .then(res => res.json())
                                            .then(data => window.ReosLogger.success('FCM Token Synced to Server', data))
                                            .catch(err => window.ReosLogger.warn('FCM token background sync offline mode', err));
                                    } else {
                                        window.ReosLogger.warn('No FCM registration token available');
                                    }
                                });
                            }
                        });
                    }).catch(err => window.ReosLogger.error('Service Worker Registration Failed', err));

                // Foreground Notification Toast
                messaging.onMessage((payload) => {
                    window.ReosLogger.info('Foreground Push Message Received', payload);
                    const title = payload.notification ? payload.notification.title : (payload.data ? payload.data.title : 'UrbanProperty Alert');
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
                        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white text-xs font-bold ml-2">âœ•</button>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => { if (toast) toast.remove(); }, 6000);
                });
            }
        }
    </script>
</body>

</html>
