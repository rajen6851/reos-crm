<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Real Estate Business – REOS SaaS</title>
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
    <div class="w-full max-w-xl space-y-6">
        <!-- Logo Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex w-12 h-12 rounded-2xl overflow-hidden shadow-xs border border-[#E2E8F0] bg-white items-center justify-center p-0.5 mb-1">
                <img src="{{ asset('images/logo.jpg') }}" alt="REOS Logo" class="w-full h-full object-cover rounded-xl">
            </div>
            <h2 class="text-2xl font-extrabold text-[#0F172A] tracking-tight">Register Your Real Estate Business</h2>
            <p class="text-xs text-[#64748B]">Onboard your company onto REOS SaaS Operating System</p>
        </div>

        <!-- Registration Form Card -->
        <div class="bg-white p-6 md:p-8 rounded-3xl space-y-6 border border-slate-200 shadow-xl">
            <!-- Errors -->
            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
                    @foreach ($errors->all() as $error)
                        <div><i class="fa-solid fa-triangle-exclamation text-rose-600 mr-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-5 text-xs">
                @csrf

                <!-- Section 1: Business Details -->
                <div class="space-y-3">
                    <div class="text-xs font-extrabold uppercase text-indigo-700 tracking-wider flex items-center space-x-2">
                        <i class="fa-solid fa-building text-indigo-600"></i>
                        <span>Business & Company Information</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Company Name *</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="Skyline Realty Infra"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Company Short Code *</label>
                            <input type="text" name="company_code" value="{{ old('company_code') }}" required placeholder="SKY"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-600 uppercase">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tax Number / GSTIN (Optional)</label>
                        <input type="text" name="tax_number" value="{{ old('tax_number') }}" placeholder="36AAACA12341ZV"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
                    </div>
                </div>

                <!-- Section 2: Owner/Admin Account -->
                <div class="space-y-3 pt-3 border-t border-slate-100">
                    <div class="text-xs font-extrabold uppercase text-indigo-700 tracking-wider flex items-center space-x-2">
                        <i class="fa-solid fa-user-tie text-indigo-600"></i>
                        <span>Company Admin Credentials</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Owner / Admin Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Vikram Mehta"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Phone Number *</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="9876543210"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Owner Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="owner@skylinerealty.com"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Password *</label>
                            <input type="password" name="password" required
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Confirm Password *</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
                        </div>
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-between">
                    <a href="{{ route('login') }}" class="text-xs text-slate-500 hover:text-indigo-600 transition">
                        Already have an account? Sign In →
                    </a>

                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-sm rounded-xl shadow-sm transition">
                        Register & Launch Workspace →
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
