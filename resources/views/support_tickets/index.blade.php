@extends('layouts.reos')

@section('title', 'Support Tickets & Helpdesk - REOS')

@section('content')
<div class="space-y-8" x-data="{ showModal: false }">

    <!-- Header Banner -->
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Customer & Team Support Desk</h1>
            <p class="text-xs text-slate-600 mt-1 font-medium">Create tickets, track issue resolutions, and collaborate with operations staff</p>
        </div>
        <div>
            <button type="button" @click="showModal = true" onclick="openSupportModal()" class="px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-md shadow-indigo-500/20 transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Raise Support Ticket</span>
            </button>
        </div>
    </div>

    <!-- Alert Message -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold flex items-center space-x-2">
        <i class="fa-solid fa-circle-check text-emerald-600"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('support-tickets.index', ['status' => 'open']) }}" class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm hover:border-indigo-300 transition">
            <div class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Open Tickets</div>
            <div class="text-2xl font-black text-amber-600 mt-1 font-mono">{{ $openCount }}</div>
            <div class="text-[11px] text-slate-500 font-bold mt-1">Awaiting Agent</div>
        </a>

        <a href="{{ route('support-tickets.index', ['status' => 'in_progress']) }}" class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm hover:border-indigo-300 transition">
            <div class="text-[10px] font-black uppercase text-slate-400 tracking-wider">In Progress</div>
            <div class="text-2xl font-black text-indigo-600 mt-1 font-mono">{{ $inProgressCount }}</div>
            <div class="text-[11px] text-slate-500 font-bold mt-1">Active Investigation</div>
        </a>

        <a href="{{ route('support-tickets.index', ['status' => 'resolved']) }}" class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm hover:border-indigo-300 transition">
            <div class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Resolved</div>
            <div class="text-2xl font-black text-emerald-600 mt-1 font-mono">{{ $resolvedCount }}</div>
            <div class="text-[11px] text-slate-500 font-bold mt-1">Solution Provided</div>
        </a>

        <a href="{{ route('support-tickets.index', ['status' => 'closed']) }}" class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm hover:border-indigo-300 transition">
            <div class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Closed</div>
            <div class="text-2xl font-black text-slate-600 mt-1 font-mono">{{ $closedCount }}</div>
            <div class="text-[11px] text-slate-500 font-bold mt-1">Completed & Closed</div>
        </a>
    </div>

    <!-- Filter Bar & Tickets Table -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <h2 class="text-lg font-black text-slate-900">Support Ticket Register</h2>
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                <a href="{{ route('support-tickets.index') }}" class="px-3 py-1.5 rounded-xl border {{ !request()->filled('status') ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200' }}">All</a>
                <a href="{{ route('support-tickets.index', ['status' => 'open']) }}" class="px-3 py-1.5 rounded-xl border {{ request('status') === 'open' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200' }}">Open</a>
                <a href="{{ route('support-tickets.index', ['status' => 'in_progress']) }}" class="px-3 py-1.5 rounded-xl border {{ request('status') === 'in_progress' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200' }}">In Progress</a>
                <a href="{{ route('support-tickets.index', ['status' => 'resolved']) }}" class="px-3 py-1.5 rounded-xl border {{ request('status') === 'resolved' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-700 border-slate-200' }}">Resolved</a>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-800 font-extrabold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-4">Ticket ID</th>
                        <th class="p-4">Subject & User</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Priority</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Updated</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $t)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-mono font-bold text-indigo-600 text-xs">
                            #{{ $t->ticket_number }}
                        </td>
                        <td class="p-4">
                            <a href="{{ route('support-tickets.show', $t->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition block">
                                {{ $t->subject }}
                            </a>
                            <div class="text-xs text-slate-500 font-medium mt-0.5">
                                Raised by: <strong>{{ $t->user->name ?? 'User' }}</strong> ({{ $t->user->email ?? 'N/A' }})
                            </div>
                        </td>
                        <td class="p-4 text-xs font-bold text-slate-700">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200">
                                <i class="fa-solid fa-folder text-slate-500 mr-1"></i>{{ $t->category }}
                            </span>
                        </td>
                        <td class="p-4 text-xs font-bold">
                            @php
                                $pBadge = match($t->priority) {
                                    'urgent' => 'bg-rose-100 text-rose-900 border-rose-300',
                                    'high' => 'bg-amber-100 text-amber-900 border-amber-300',
                                    'medium' => 'bg-sky-100 text-sky-900 border-sky-300',
                                    default => 'bg-slate-100 text-slate-800 border-slate-300'
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full border {{ $pBadge }}">
                                {{ strtoupper($t->priority) }}
                            </span>
                        </td>
                        <td class="p-4 text-xs font-bold">
                            @php
                                $sBadge = match($t->status) {
                                    'open' => 'bg-amber-100 text-amber-900 border-amber-300',
                                    'in_progress' => 'bg-indigo-100 text-indigo-900 border-indigo-300',
                                    'resolved' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                                    'closed' => 'bg-slate-200 text-slate-800 border-slate-400',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full border {{ $sBadge }}">
                                ● {{ strtoupper(str_replace('_', ' ', $t->status)) }}
                            </span>
                        </td>
                        <td class="p-4 text-xs text-slate-500 font-mono">
                            {{ $t->updated_at ? $t->updated_at->diffForHumans() : 'N/A' }}
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('support-tickets.show', $t->id) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200 text-xs font-bold transition inline-block">
                                View & Reply ➔
                            </a>

                            @if(auth()->user()->isCompanyAdmin() || auth()->user()->role?->slug === 'founder')
                            <form method="POST" action="{{ route('support-tickets.destroy', $t->id) }}" onsubmit="return confirm('Are you sure you want to delete support ticket #{{ $t->ticket_number }}?');" class="inline-block ml-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-rose-500 mr-1"></i>Delete
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500 font-medium text-xs">
                            No support tickets recorded yet. Click "Raise Support Ticket" to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4">
            {{ $tickets->links() }}
        </div>
    </div>

    <!-- Create Ticket Modal -->
    <div id="createTicketModal" x-show="showModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-cloak @click.self="showModal = false; closeSupportModal()">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 md:p-8 shadow-2xl border border-slate-200 space-y-6" @click.stop>
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-xl font-black text-slate-900">Raise New Support Ticket</h3>
                <button type="button" @click="showModal = false" onclick="closeSupportModal()" class="p-2 text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">✕</button>
            </div>

            <form action="{{ route('support-tickets.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Destination / Recipient Info Box -->
                @if(auth()->user()->isBroker())
                <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-200 space-y-2">
                    <label class="block text-xs font-black uppercase text-indigo-900">Select Target Builder Company (Recipient) *</label>
                    <select name="company_id" required class="w-full bg-white border border-indigo-200 rounded-2xl p-3 text-xs font-bold text-slate-900 focus:border-indigo-600 focus:ring-indigo-600 shadow-2xs">
                        <option value="">-- Choose Target Builder Company --</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-indigo-700 font-medium">आप स्वतंत्र ब्रोकर हैं — आप जिस कंपनी को चुनेंगे, टिकट सीधे उसी कंपनी के सपोर्ट एडमिन को प्राप्त होगा।</p>
                </div>
                @else
                <div class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-200 text-xs text-indigo-950 space-y-1">
                    <div class="font-extrabold flex items-center space-x-1.5 text-indigo-900">
                        <span>Ticket Recipient & Destination:</span>
                    </div>
                    <div class="font-bold flex items-center space-x-2 text-indigo-800">
                        <i class="fa-solid fa-building text-indigo-600"></i>
                        <span>{{ auth()->user()->company->name ?? 'REOS Central' }} Support & Operations Team</span>
                    </div>
                    <p class="text-[11px] text-indigo-700 font-medium">यह टिकट सबमिट होने के बाद आपकी कंपनी के सपोर्ट मैनेजर्स व एडमिन को रिज़ॉल्यूशन के लिए तुरंत दिखेगा।</p>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-extrabold uppercase text-slate-700 mb-1">Issue Subject</label>
                    <input type="text" name="subject" required placeholder="e.g. Lead assignment not updating / Payment status issue" class="w-full rounded-2xl border-slate-200 text-xs font-bold focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-extrabold uppercase text-slate-700 mb-1">Issue Category</label>
                        <select name="category" required class="w-full rounded-2xl border-slate-200 text-xs font-bold focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Technical">Technical Issue</option>
                            <option value="Billing">Billing & Subscription</option>
                            <option value="Inventory">Inventory / Units</option>
                            <option value="Lead/CRM">Lead / CRM Pipeline</option>
                            <option value="General" selected>General Query</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold uppercase text-slate-700 mb-1">Priority</label>
                        <select name="priority" required class="w-full rounded-2xl border-slate-200 text-xs font-bold focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>


                <div>
                    <label class="block text-xs font-extrabold uppercase text-slate-700 mb-1">Detailed Description</label>
                    <textarea name="description" rows="4" required placeholder="Provide step-by-step details about the issue or request..." class="w-full rounded-2xl border-slate-200 text-xs font-medium focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="showModal = false" onclick="closeSupportModal()" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-md shadow-indigo-500/20 cursor-pointer">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function openSupportModal() {
        const modal = document.getElementById('createTicketModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
    function closeSupportModal() {
        const modal = document.getElementById('createTicketModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }
</script>
@endsection
