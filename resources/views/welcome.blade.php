<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>REOS – Real Estate Operating System SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body { font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-[#0F172A] min-h-screen flex flex-col justify-between selection:bg-[#059669] selection:text-white">
    <!-- Navbar -->
    <nav class="max-w-7xl w-full mx-auto px-6 py-6 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-2xl overflow-hidden shadow-xs border border-emerald-200 bg-white flex items-center justify-center p-0.5">
                <img src="{{ asset('images/logo.jpg') }}" alt="REOS Logo" class="w-full h-full object-cover rounded-xl">
            </div>
            <div>
                <span class="text-xl font-extrabold tracking-tight text-[#0F172A]">REOS</span>
                <span class="text-[10px] uppercase font-bold tracking-widest text-[#059669] block -mt-1">Real Estate OS</span>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-[#059669] hover:bg-[#047857] text-white font-bold text-xs rounded-xl shadow-xs transition">
                        Go to Dashboard →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-bold text-[#0F172A] hover:text-[#059669] transition px-3 py-2">
                        Sign In
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-5 py-2.5 bg-[#059669] hover:bg-[#047857] text-white font-bold text-xs rounded-xl shadow-xs transition">
                            Register Company →
                        </a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="max-w-4xl mx-auto px-6 text-center space-y-8 my-auto py-12">
        <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-[#059669] border border-emerald-200">
            <i class="fa-solid fa-bolt text-[#059669]"></i>
            <span>Multi-Tenant Enterprise Operating System for Real Estate</span>
        </div>

        <h1 class="text-4xl sm:text-6xl font-extrabold text-[#0F172A] tracking-tight leading-tight">
            The Complete Operating System for <span class="text-[#059669]">Real Estate Companies</span>
        </h1>

        <p class="text-base text-[#64748B] max-w-2xl mx-auto leading-relaxed">
            Manage your CRM pipeline, sales executives, property inventory locking, channel partner brokers, and automated payment receipts in one unified workspace.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-[#059669] hover:bg-[#047857] text-white font-bold text-sm rounded-2xl shadow-sm transition">
                Register Your Real Estate Business →
            </a>
            <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-white hover:bg-slate-100 text-[#0F172A] border border-[#E2E8F0] font-bold text-sm rounded-2xl transition">
                Sign In to Workspace
            </a>
        </div>

        <!-- Quick Features Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-12 text-left">
            <div class="bg-white p-6 rounded-2xl border border-[#E2E8F0] shadow-xs space-y-2">
                <div class="text-[#059669] text-2xl"><i class="fa-solid fa-building"></i></div>
                <h3 class="font-bold text-[#0F172A] text-sm">Company Onboarding</h3>
                <p class="text-xs text-[#64748B]">Instant tenant company workspace registration with custom team roles.</p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-[#E2E8F0] shadow-xs space-y-2">
                <div class="text-[#059669] text-2xl"><i class="fa-solid fa-layer-group"></i></div>
                <h3 class="font-bold text-[#0F172A] text-sm">Inventory Locking</h3>
                <p class="text-xs text-[#64748B]">Pessimistic row locking to prevent double-booking of units.</p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-[#E2E8F0] shadow-xs space-y-2">
                <div class="text-[#059669] text-2xl"><i class="fa-solid fa-handshake"></i></div>
                <h3 class="font-bold text-[#0F172A] text-sm">Broker Portal</h3>
                <p class="text-xs text-[#64748B]">Channel partner portal with sanitized client lead tracking & payouts.</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="max-w-7xl w-full mx-auto px-6 py-6 border-t border-[#E2E8F0] text-center sm:flex items-center justify-between text-xs text-[#64748B]">
        <div>
            © {{ date('Y') }} <strong>REOS Platform</strong>. All rights reserved.
        </div>
        <div class="mt-2 sm:mt-0 font-medium">
            Multi-Tenant Enterprise Operating System SaaS
        </div>
    </footer>
</body>
</html>
