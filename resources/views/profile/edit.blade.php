@extends('layouts.reos')

@section('title', 'Profile Settings - REOS')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-user text-2xl text-indigo-600"></i>
                <h1 class="text-2xl font-black text-slate-900">User Profile Settings</h1>
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium">Update your account profile details, phone number, and security password</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 bg-indigo-50 border border-indigo-200 px-3.5 py-2 rounded-2xl">
            <span class="text-indigo-900">Role: {{ auth()->user()->role->name ?? 'Account User' }}</span>
        </div>
    </div>

    <!-- Update Profile Information -->
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-6">
        <div class="border-b border-slate-100 pb-3">
            <h2 class="text-lg font-black text-slate-900">Profile Information</h2>
            <p class="text-xs text-slate-500">Update your name, phone number, and email address.</p>
        </div>

        @if (session('status') === 'profile-updated')
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-2xl flex items-center space-x-1.5">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>Profile details updated successfully!</span>
            </div>
        @endif

        <form method="post" action="{{ route('profile.update') }}" class="space-y-4 text-xs">
            @csrf
            @method('patch')

            <div>
                <label for="name" class="block text-slate-700 mb-1 font-bold">Full Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                @error('name')
                    <p class="text-rose-600 text-[11px] mt-1 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-slate-700 mb-1 font-bold">Email Address *</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                    @error('email')
                        <p class="text-rose-600 text-[11px] mt-1 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-slate-700 mb-1 font-bold">Phone Number</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" autocomplete="tel" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                    @error('phone')
                        <p class="text-rose-600 text-[11px] mt-1 font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl shadow-xs transition">
                    Save Profile Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Update Password -->
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-6">
        <div class="border-b border-slate-100 pb-3">
            <h2 class="text-lg font-black text-slate-900">Update Password</h2>
            <p class="text-xs text-slate-500">Ensure your account is using a long, random password to stay secure.</p>
        </div>

        <form method="post" action="{{ route('password.update') }}" class="space-y-4 text-xs">
            @csrf
            @method('put')

            <div>
                <label for="update_password_current_password" class="block text-slate-700 mb-1 font-bold">Current Password *</label>
                <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                @error('current_password')
                    <p class="text-rose-600 text-[11px] mt-1 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="update_password_password" class="block text-slate-700 mb-1 font-bold">New Password *</label>
                    <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                    @error('password')
                        <p class="text-rose-600 text-[11px] mt-1 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="update_password_password_confirmation" class="block text-slate-700 mb-1 font-bold">Confirm New Password *</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                    @error('password_confirmation')
                        <p class="text-rose-600 text-[11px] mt-1 font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-extrabold rounded-xl shadow-xs transition">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
