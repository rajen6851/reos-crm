<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – REOS Real Estate Operating System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-[#0F172A] min-h-screen flex flex-col justify-center items-center p-4">
    <div class="w-full max-w-md space-y-6">
        <!-- Logo Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex w-12 h-12 rounded-2xl overflow-hidden shadow-xs border border-[#E2E8F0] bg-white items-center justify-center p-0.5 mb-1">
                <img src="{{ asset('images/logo.jpg') }}" alt="REOS Logo" class="w-full h-full object-cover rounded-xl">
            </div>
            <h2 class="text-2xl font-extrabold text-[#0F172A] tracking-tight">Sign In to REOS</h2>
            <p class="text-xs text-[#64748B]">Real Estate Operating System SaaS Workspace</p>
        </div>

        <!-- Login Form Card -->
        <div class="bg-white p-6 md:p-8 rounded-3xl space-y-6 border border-slate-200 shadow-xl">
            <!-- Session Status -->
            @if (session('status'))
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Errors -->
            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
                    @foreach ($errors->all() as $error)
                        <div><i class="fa-solid fa-triangle-exclamation text-rose-600 mr-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label for="email" class="block font-bold text-slate-700 mb-1.5">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="font-bold text-slate-700">Password</label>
                    </div>
                    <input id="password" type="password" name="password" required
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition">
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 bg-slate-50 text-emerald-600 focus:ring-emerald-600">
                        <span class="text-slate-600 font-medium">Remember me</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm rounded-xl shadow-sm transition">
                    Sign In to Dashboard →
                </button>
            </form>

            <!-- Quick Demo Login Presets -->
            <div class="border-t border-slate-100 pt-5 space-y-3">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center"><i class="fa-solid fa-bolt text-emerald-600 mr-1"></i>1-Click Role-Specific Dashboards</div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="quickLogin('founder@reos.com')" class="p-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-indigo-700 font-bold rounded-xl text-left transition col-span-2">
                        <i class="fa-solid fa-crown text-amber-500 mr-1"></i>SaaS Super Admin
                        <span class="block text-[10px] text-slate-500 font-normal">Platform Revenue, SaaS Plans & Tenant Companies</span>
                    </button>

                    <button type="button" onclick="quickLogin('director@apexrealty.com')" class="p-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-blue-700 font-bold rounded-xl text-left transition">
                        <i class="fa-solid fa-building-columns text-blue-600 mr-1"></i>Founder / Director
                        <span class="block text-[10px] text-slate-500 font-normal">Full Company & Strategic Control</span>
                    </button>

                    <button type="button" onclick="quickLogin('admin@apexrealty.com')" class="p-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-purple-700 font-bold rounded-xl text-left transition">
                        <i class="fa-solid fa-building text-purple-600 mr-1"></i>Admin
                        <span class="block text-[10px] text-slate-500 font-normal">Team & Projects Control</span>
                    </button>

                    <button type="button" onclick="quickLogin('manager@apexrealty.com')" class="p-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-emerald-700 font-bold rounded-xl text-left transition">
                        <i class="fa-solid fa-user-tie text-emerald-600 mr-1"></i>Manager
                        <span class="block text-[10px] text-slate-500 font-normal">CRM & Live Inventory</span>
                    </button>

                    <button type="button" onclick="quickLogin('sales@apexrealty.com')" class="p-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-amber-800 font-bold rounded-xl text-left transition">
                        <i class="fa-solid fa-briefcase text-amber-700 mr-1"></i>Sales Executive
                        <span class="block text-[10px] text-slate-500 font-normal">Assigned Leads & Calls</span>
                    </button>

                    <button type="button" onclick="quickLogin('broker@apexrealty.com')" class="p-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-sky-700 font-bold rounded-xl text-left transition col-span-2 sm:col-span-1">
                        <i class="fa-solid fa-handshake text-sky-600 mr-1"></i>Broker
                        <span class="block text-[10px] text-slate-500 font-normal">Submit Leads & Track Status</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quickLogin(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password123';
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html>
