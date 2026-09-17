@extends('layouts.reos')

@section('title', 'My Permissions - REOS')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-shield-halved text-2xl text-indigo-600"></i>
                <h1 class="text-2xl font-black text-slate-900">My Access Permissions</h1>
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium">Review the modules and features you have access to in the REOS platform.</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 bg-indigo-50 border border-indigo-200 px-3.5 py-2 rounded-2xl">
            <span class="text-indigo-900">Role: {{ $user->isSaaSAdmin() ? ($user->is_super_admin ? 'SaaS Founder' : 'SaaS Sub-Admin') : ($user->role->name ?? 'Account User') }}</span>
        </div>
    </div>

    <!-- My Permissions Badges Grouped by Module -->
    <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-8">
        @if($user->isSaaSAdmin() && $user->is_super_admin)
            <div class="p-4 bg-purple-50 border border-purple-200 rounded-xl mb-4">
                <div class="flex items-center space-x-2 text-purple-800 font-bold">
                    <i class="fa-solid fa-crown"></i>
                    <span>SaaS Founder Master Access</span>
                </div>
                <p class="text-xs text-purple-700 mt-1">As a SaaS Founder, you have full unrestricted access to all platform features, configuration, and tenant settings.</p>
            </div>
        @elseif(!$user->isSaaSAdmin() && $user->isDirectorOrFounder())
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl mb-4">
                <div class="flex items-center space-x-2 text-emerald-800 font-bold">
                    <i class="fa-solid fa-crown"></i>
                    <span>Director / Founder Access</span>
                </div>
                <p class="text-xs text-emerald-700 mt-1">As a Director/Founder, you have unrestricted access to all features across all modules in the platform.</p>
            </div>
        @endif

        @forelse($allPermissionsGrouped as $module => $permissions)
            <div>
                <div class="border-b border-slate-100 pb-3 mb-4">
                    <h2 class="text-lg font-black text-slate-900">Module: {{ $module }}</h2>
                    <p class="text-xs text-slate-500">{{ $permissions->count() }} Features Available</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($permissions as $perm)
                        @php $hasIt = in_array($perm->slug, $myPermissions); @endphp
                        <div class="p-4 rounded-xl border transition-all flex items-start space-x-3
                            {{ $hasIt ? 'bg-indigo-50/50 border-indigo-200 shadow-sm' : 'bg-slate-50 border-slate-200 opacity-60 grayscale' }}">
                            <div class="mt-0.5">
                                @if($hasIt)
                                    <i class="fa-solid fa-circle-check text-indigo-600 text-lg"></i>
                                @else
                                    <i class="fa-solid fa-lock text-slate-400 text-lg"></i>
                                @endif
                            </div>
                            <div>
                                <div class="font-bold text-sm {{ $hasIt ? 'text-indigo-900' : 'text-slate-600' }}">
                                    {{ $perm->name }}
                                </div>
                                <div class="text-[11px] mt-0.5 {{ $hasIt ? 'text-indigo-700' : 'text-slate-500' }}">
                                    {{ $perm->description }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-slate-500 italic">No permissions defined in the system.</div>
        @endforelse
    </div>
</div>
@endsection
