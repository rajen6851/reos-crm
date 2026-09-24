@extends('layouts.reos')

@section('title', 'System Alerts & Notifications - UrbanProperty')

@section('content')
<div class="space-y-6 pb-12" x-data="{ showBroadcastModal: false, title: '', message: '', target_audience: 'all' }">
    
    <!-- Premium Header Area -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-3xl p-8 shadow-xl relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500 rounded-full blur-[100px] opacity-40"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-emerald-500 rounded-full blur-[100px] opacity-30"></div>
        
        <div class="relative z-10 space-y-2">
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-400 mb-1 tracking-wider uppercase">
                <a href="{{ route('dashboard') }}" class="hover:text-white transition">Home</a>
                <span>&bull;</span>
                <span class="text-white">Alerts Hub</span>
            </div>
            <h1 class="text-3xl font-black tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm border border-white/20">
                    <i class="fa-solid fa-bell text-white text-lg"></i>
                </div>
                Notifications Center
            </h1>
            <p class="text-slate-300 text-sm font-medium max-w-xl">
                Stay updated with real-time system alerts, lead activity logs, and important platform announcements.
            </p>
        </div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-4">
            <div class="flex items-center space-x-2 text-[11px] font-bold text-emerald-400 bg-emerald-400/10 border border-emerald-400/20 px-4 py-2 rounded-full backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>FCM Engine Active</span>
            </div>
            
            @if(auth()->user()->isCompanyAdmin() || auth()->user()->isSaaSFounder())
            <button @click="showBroadcastModal = true" class="px-6 py-3 bg-white text-slate-900 hover:bg-slate-100 font-black text-xs rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center space-x-2 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-bullhorn text-indigo-600"></i>
                <span>Broadcast Push</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Feed Section -->
    <div class="bg-white/80 backdrop-blur-xl border border-slate-200 rounded-3xl shadow-sm p-2">
        <div class="p-5 border-b border-slate-100/60 flex items-center justify-between">
            <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-500"></i> Activity Feed
            </h3>
            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-bold tracking-wider uppercase border border-slate-200">
                Latest Logs
            </span>
        </div>

        <div class="p-2 space-y-2">
            @forelse($notifications as $n)
            <div class="group p-4 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all duration-300">
                <div class="flex items-start sm:items-center space-x-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
                        <i class="fa-solid fa-bell text-sm"></i>
                    </div>
                    <div>
                        <div class="text-sm font-extrabold text-slate-800 leading-snug group-hover:text-indigo-700 transition-colors">
                            {{ $n->description }}
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-[11px] font-medium text-slate-500 mt-1.5">
                            <span class="flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 text-slate-600">
                                <i class="fa-regular fa-user"></i> {{ $n->user->name ?? 'System' }}
                            </span>
                            @if(isset($n->lead))
                            <span class="flex items-center gap-1 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 text-indigo-600 font-bold">
                                <i class="fa-solid fa-bullseye"></i> {{ $n->lead->name }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2 text-[11px] font-bold text-slate-400 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100 shrink-0">
                    <i class="fa-regular fa-clock"></i>
                    <span>{{ $n->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @empty
            <div class="py-16 flex flex-col items-center justify-center text-center opacity-60">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4 border border-slate-200">
                    <i class="fa-regular fa-bell-slash text-2xl"></i>
                </div>
                <h4 class="text-slate-800 font-bold text-sm">No notifications yet</h4>
                <p class="text-slate-500 text-xs mt-1 max-w-xs">You're all caught up! System alerts and logs will appear here.</p>
            </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
        <div class="p-4 border-t border-slate-100/60">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>

    <!-- Broadcast Modal -->
    <div x-show="showBroadcastModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4 transition-opacity" x-transition.opacity>
        <div @click.away="showBroadcastModal = false" class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden" x-show="showBroadcastModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4">
            
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-6 text-white flex justify-between items-center relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                <div class="relative z-10">
                    <h3 class="text-lg font-black flex items-center gap-2">
                        <i class="fa-solid fa-satellite-dish"></i> Broadcast Push
                    </h3>
                    <p class="text-emerald-100 text-[11px] font-medium mt-1">Send a global alert to your team</p>
                </div>
                <button @click="showBroadcastModal = false" class="relative z-10 w-8 h-8 flex items-center justify-center rounded-full bg-black/10 hover:bg-black/20 text-white transition"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="p-6 md:p-8 space-y-6 bg-slate-50">
                <!-- Presets -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-3">Quick Presets</label>
                    <div class="grid grid-cols-2 gap-3 text-xs font-bold">
                        <button type="button" @click="title = 'Happy Holi!'; message = 'Wishing you and your family a colorful and safe Holi!'"
                            class="p-3 bg-white hover:bg-pink-50 text-slate-700 hover:text-pink-600 border border-slate-200 hover:border-pink-200 rounded-xl transition flex flex-col items-center gap-2 shadow-sm hover:shadow">
                            <i class="fa-solid fa-face-smile-beam text-pink-500 text-lg"></i>
                            <span>Festival</span>
                        </button>
                        <button type="button" @click="title = 'Target Achieved!'; message = 'Congratulations team! We have successfully hit our monthly booking target!'"
                            class="p-3 bg-white hover:bg-amber-50 text-slate-700 hover:text-amber-600 border border-slate-200 hover:border-amber-200 rounded-xl transition flex flex-col items-center gap-2 shadow-sm hover:shadow">
                            <i class="fa-solid fa-trophy text-amber-500 text-lg"></i>
                            <span>Milestone</span>
                        </button>
                        <button type="button" @click="title = 'Meeting Alert'; message = 'Please join the mandatory all-hands sales huddle in 15 minutes.'"
                            class="p-3 bg-white hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 border border-slate-200 hover:border-indigo-200 rounded-xl transition flex flex-col items-center gap-2 shadow-sm hover:shadow">
                            <i class="fa-solid fa-users text-indigo-500 text-lg"></i>
                            <span>Meeting</span>
                        </button>
                        <button type="button" @click="title = 'System Update'; message = 'The CRM platform will undergo brief maintenance tonight at 11 PM.'"
                            class="p-3 bg-white hover:bg-sky-50 text-slate-700 hover:text-sky-600 border border-slate-200 hover:border-sky-200 rounded-xl transition flex flex-col items-center gap-2 shadow-sm hover:shadow">
                            <i class="fa-solid fa-wrench text-sky-500 text-lg"></i>
                            <span>Maintenance</span>
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('notifications.broadcast') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1.5">Notification Title *</label>
                        <input type="text" name="title" x-model="title" required maxlength="50" class="w-full border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm" placeholder="e.g. Important Announcement">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1.5">Notification Message *</label>
                        <textarea name="message" x-model="message" required rows="3" maxlength="200" class="w-full border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm resize-none leading-relaxed" placeholder="Type your message here..."></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1.5">Target Audience</label>
                        <select name="target_audience" x-model="target_audience" class="w-full border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">
                            <option value="all">All Company Staff (Global)</option>
                            <option value="manager">Managers Only</option>
                            <option value="executive">Sales Executives Only</option>
                            <option value="broker">Brokers Only</option>
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl shadow-lg hover:shadow-emerald-500/30 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i> Send Push Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
