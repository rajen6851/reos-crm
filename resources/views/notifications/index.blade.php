@extends('layouts.reos')

@section('title', 'System Alerts & Notifications - REOS')

@section('content')
<div class="space-y-8" x-data="{ showBroadcastModal: false, title: '', message: '', target: 'all' }">
    <!-- Header & Action Bar -->
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-bell text-emerald-600"></i>
                <span>System Alerts & Push Notifications</span>
            </h1>
            <p class="text-xs text-slate-600 mt-1 font-medium">Real-time alerts, lead logs, and platform broadcast push notifications</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(auth()->user()->isCompanyAdmin() || auth()->user()->isManager() || auth()->user()->isSaaSFounder())
            <button @click="showBroadcastModal = true" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-bullhorn text-xs"></i>
                <span>Broadcast Push Notification</span>
            </button>
            @endif
            <div class="hidden sm:flex items-center space-x-2 text-xs font-bold text-slate-700 bg-indigo-50 border border-indigo-200 px-3.5 py-3 rounded-xl">
                <i class="fa-solid fa-bolt text-indigo-600 mr-1"></i><span>Live FCM Engine Active</span>
            </div>
        </div>
    </div>

    <!-- Notifications List Feed -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-sm font-extrabold text-slate-900">Activity & Alert Logs Feed</h3>
            <span class="text-xs text-slate-500 font-medium">Showing latest 20 notifications</span>
        </div>

        @forelse($notifications as $n)
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-4 hover:bg-white transition">
            <div class="flex items-start space-x-3">
                <div class="w-9 h-9 rounded-2xl bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center justify-center mt-0.5 shrink-0">
                    <i class="fa-solid fa-bell text-indigo-600"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-900 leading-snug">{{ $n->description }}</div>
                    <div class="text-[11px] text-slate-500 font-mono mt-1">
                        Triggered by <span class="font-bold text-slate-700">{{ $n->user->name ?? 'System' }}</span> for Lead <span class="font-bold text-indigo-600">{{ $n->lead->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="text-[11px] text-slate-400 font-mono whitespace-nowrap">
                {{ $n->created_at->diffForHumans() }}
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-slate-500 font-medium text-xs rounded-2xl bg-slate-50 border border-slate-200">
            No system notifications recorded yet.
        </div>
        @endforelse

        <div class="pt-4">
            {{ $notifications->links() }}
        </div>
    </div>

    <!-- Broadcast Push Notification Modal -->
    <div x-show="showBroadcastModal" x-cloak class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-lg w-full border border-slate-200 shadow-2xl space-y-6">
            <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-bullhorn text-emerald-600"></i>
                        <span>Send Broadcast Push Notification</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Send real-time FCM push alert & RTDB announcement to users</p>
                </div>
                <button @click="showBroadcastModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <!-- Quick Festival & Event Presets -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">⚡ 1-Click Quick Presets</label>
                <div class="grid grid-cols-2 gap-2 text-xs font-bold">
                    <button type="button" @click="title = '🌸 Happy Holi from REOS!'; message = 'Wishing you, your family, and team a colorful, safe & prosperous Holi! ✨'"
                        class="p-2.5 bg-pink-50 hover:bg-pink-100 text-pink-700 border border-pink-200 rounded-xl transition text-left flex items-center space-x-2">
                        <span>🌸 Happy Holi</span>
                    </button>
                    <button type="button" @click="title = '🪔 Happy Diwali from REOS!'; message = 'May this festival of lights bring prosperity, joy, and success to your real estate business! 🌟'"
                        class="p-2.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 rounded-xl transition text-left flex items-center space-x-2">
                        <span>🪔 Happy Diwali</span>
                    </button>
                    <button type="button" @click="title = '📢 System Announcement'; message = 'Important update: Please review your daily lead follow-ups and site visit schedules.'"
                        class="p-2.5 bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200 rounded-xl transition text-left flex items-center space-x-2">
                        <span>📢 Maintenance Alert</span>
                    </button>
                    <button type="button" @click="title = '🎯 Target Milestone Achieved!'; message = 'Congratulations team on achieving our monthly sales booking target! Keep up the great momentum! 🏆'"
                        class="p-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl transition text-left flex items-center space-x-2">
                        <span>🎯 Sales Milestone</span>
                    </button>
                </div>
            </div>

            <!-- Broadcast Form -->
            <form method="POST" action="{{ route('notifications.broadcast') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Target Audience</label>
                    <select name="target_audience" x-model="target" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                        <option value="all">🌐 All Platform Users (Staff, Admins & Brokers)</option>
                        <option value="admin">🏢 Admins & Directors Only</option>
                        <option value="manager">💼 Sales Managers Only</option>
                        <option value="executive">🎧 Sales Executives Only</option>
                        <option value="broker">🤝 Channel Partner Brokers Only</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Notification Title</label>
                    <input type="text" name="title" x-model="title" required placeholder="e.g. 🌸 Happy Holi from REOS!" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Notification Message</label>
                    <textarea name="message" x-model="message" required rows="3" placeholder="Enter notification body text..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:outline-none focus:border-emerald-600 focus:bg-white transition"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showBroadcastModal = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">Cancel</button>
                    <button type="submit" :disabled="!title || !message" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-extrabold text-xs rounded-xl shadow-xs transition flex items-center space-x-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Send Broadcast Now</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
