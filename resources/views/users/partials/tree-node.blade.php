@foreach($users as $user)
    <li class="relative pl-6 py-2 border-l border-slate-300 last:border-l-0 before:content-[''] before:absolute before:w-4 before:h-[1px] before:bg-slate-300 before:left-0 before:top-5" x-data="{ expanded: true }">
        <div class="flex items-center space-x-2 relative z-10">
            @if($user->children->count() > 0)
                <button @click="expanded = !expanded" class="w-5 h-5 flex items-center justify-center rounded bg-slate-100 hover:bg-slate-200 text-slate-500 border border-slate-300 cursor-pointer transition z-20 shrink-0">
                    <i class="fa-solid fa-caret-right transition-transform text-[10px]" :class="expanded ? 'rotate-90' : ''"></i>
                </button>
            @else
                <div class="w-5 h-5 shrink-0"></div>
            @endif
            
            <div class="flex items-center space-x-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-xs hover:border-slate-300 transition">
                @php
                    $roleColor = 'text-slate-600 bg-slate-100';
                    $roleIcon = 'fa-user';
                    $roleSlug = strtolower($user->role->name ?? '');
                    if (str_contains($roleSlug, 'admin') || str_contains($roleSlug, 'director') || str_contains($roleSlug, 'founder')) {
                        $roleColor = 'text-indigo-600 bg-indigo-50';
                        $roleIcon = 'fa-user-tie';
                    } elseif (str_contains($roleSlug, 'manager')) {
                        $roleColor = 'text-emerald-600 bg-emerald-50';
                        $roleIcon = 'fa-briefcase';
                    } elseif (str_contains($roleSlug, 'executive') || str_contains($roleSlug, 'sales')) {
                        $roleColor = 'text-sky-600 bg-sky-50';
                        $roleIcon = 'fa-headset';
                    }
                @endphp
                <span class="w-6 h-6 rounded flex items-center justify-center {{ $roleColor }} border border-white/20 shadow-xs shrink-0">
                    <i class="fa-solid {{ $roleIcon }} text-[10px]"></i>
                </span>
                <span class="text-xs font-bold text-slate-700 whitespace-nowrap">{{ $user->name }}</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $roleColor }} border border-current opacity-70 font-semibold whitespace-nowrap">{{ $user->role->name ?? 'User' }}</span>
                
                @if($user->children->count() > 0)
                <span class="text-[10px] text-slate-400 font-mono ml-2 whitespace-nowrap">({{ $user->children->count() }} members)</span>
                @endif
            </div>
        </div>

        @if($user->children->count() > 0)
            <ul x-show="expanded" x-collapse class="mt-2 ml-2">
                @include('users.partials.tree-node', ['users' => $user->children])
            </ul>
        @endif
    </li>
@endforeach
