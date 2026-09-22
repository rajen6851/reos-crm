@extends('layouts.reos')

@section('title', 'Lead Integration Engine & Webhooks - UrbanProperty')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Breadcrumb & Header Banner -->
    <div class="reos-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white border border-slate-200 rounded-xl shadow-xs">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#2563EB]">Home</a>
                <span>â€º</span>
                <a href="{{ route('company-settings.index') }}" class="hover:text-[#2563EB]">Settings</a>
                <span>â€º</span>
                <span class="text-[#0F172A] font-bold">Lead Sources Integration Engine</span>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg shadow-xs">
                    <i class="fa-solid fa-plug"></i>
                </div>
                <div>
                    <h1 class="page-heading text-xl font-extrabold text-slate-900 tracking-tight">UrbanProperty Lead Integration Engine</h1>
                    <p class="body-text text-xs text-slate-500 font-medium">Multi-tenant webhook pipeline for Meta Ads, Google Ads, 99acres, MagicBricks, Housing.com & Custom APIs.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="openAddSourceModal()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition-all duration-200 flex items-center space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Add Lead Source</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Metric Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="reos-card p-4 bg-white border border-slate-200 rounded-xl shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-network-wired"></i>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500">Connected Channels</div>
                <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $leadSources->where('status', 'connected')->count() }} / {{ $leadSources->count() }}</div>
            </div>
        </div>

        <div class="reos-card p-4 bg-white border border-slate-200 rounded-xl shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500">Integration Health</div>
                <div class="text-xl font-extrabold text-emerald-600 mt-0.5">
                    {{ $leadSources->where('status', 'error')->count() > 0 ? 'Action Needed' : '100% Operational' }}
                </div>
            </div>
        </div>

        <div class="reos-card p-4 bg-white border border-slate-200 rounded-xl shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500">Active Webhook Listeners</div>
                <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $leadSources->where('is_active', true)->count() }} Active</div>
            </div>
        </div>

        <div class="reos-card p-4 bg-white border border-slate-200 rounded-xl shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-[#0F172A] fa-diagram-project"></i>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500">Auto Round-Robin</div>
                <div class="text-xl font-extrabold text-slate-900 mt-0.5">Level 1 & 2 Active</div>
            </div>
        </div>
    </div>

    <!-- Supported Integration Channels Grid -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                <i class="fa-solid fa-cubes text-indigo-600"></i>
                <span>Available Connectors</span>
            </h2>
            <span class="text-xs text-slate-500">6 Providers Supported</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($supportedTypes as $typeKey => $typeInfo)
                @php
                    $configuredSources = $leadSources->where('type', $typeKey);
                    $isConfigured = $configuredSources->isNotEmpty();
                @endphp
                <div class="reos-card p-5 bg-white border border-slate-200 hover:border-indigo-300 rounded-xl shadow-xs transition duration-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-lg text-indigo-600 font-bold">
                                    <i class="{{ $typeInfo['icon'] }}"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">{{ $typeInfo['name'] }}</h3>
                                    <span class="text-[11px] text-slate-400 font-mono">Code: {{ $typeKey }}</span>
                                </div>
                            </div>
                            @if($isConfigured)
                                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $configuredSources->count() }} Active
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 text-[11px] font-medium rounded-full bg-slate-100 text-slate-500 border border-slate-200">
                                    Inactive
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">{{ $typeInfo['description'] }}</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400 font-medium">Auto-deduplicated</span>
                        <button onclick="openConfigureModal('{{ $typeKey }}')" class="px-3 py-1.5 bg-slate-900 hover:bg-indigo-600 text-white text-xs font-bold rounded-lg transition duration-200 flex items-center space-x-1.5">
                            <i class="fa-solid fa-sliders text-[10px]"></i>
                            <span>Configure</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Active Lead Sources Table Card -->
    <div class="reos-card bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/50">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-list-check text-indigo-600"></i>
                    <span>Configured Webhook Endpoints & Integrations</span>
                </h2>
                <p class="text-xs text-slate-500">Live Webhook URLs and company-scoped credentials connected to UrbanProperty.</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold px-3 py-1 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-lg">
                    Total: {{ $leadSources->count() }} Source Channels
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100/80 text-slate-700 uppercase font-bold border-b border-slate-200 text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">Source Name</th>
                        <th class="px-5 py-3.5">Type</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Inbound Webhook Endpoint</th>
                        <th class="px-5 py-3.5">Default Project</th>
                        <th class="px-5 py-3.5">Last Sync</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($leadSources as $source)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-bold text-slate-900">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 text-indigo-600 flex items-center justify-center font-bold">
                                        <i class="{{ $supportedTypes[$source->type]['icon'] ?? 'fa-solid fa-globe' }}"></i>
                                    </div>
                                    <div>
                                        <div class="text-slate-900 font-bold text-sm">{{ $source->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">Token: {{ substr($source->webhook_token, 0, 8) }}...</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="capitalize px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $source->type }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if($source->status === 'connected')
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center space-x-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Connected</span>
                                    </span>
                                @elseif($source->status === 'error')
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200 inline-flex items-center space-x-1.5" title="{{ $source->error_log }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Error</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center space-x-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Testing</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center space-x-2">
                                    <input type="text" readonly value="{{ $source->webhook_url }}" class="w-64 text-[11px] bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1 text-slate-700 font-mono focus:outline-none select-all">
                                    <button onclick="copyWebhookUrl('{{ $source->webhook_url }}')" class="px-2 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-[11px] font-bold transition flex items-center space-x-1" title="Copy URL">
                                        <i class="fa-solid fa-copy"></i>
                                        <span>Copy</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600 font-medium">
                                @php
                                    $projId = $source->settings['default_project_id'] ?? null;
                                    $proj = $projId ? $projects->firstWhere('id', $projId) : null;
                                @endphp
                                {{ $proj ? $proj->name : 'Auto-Detect' }}
                            </td>
                            <td class="px-5 py-4 text-slate-500 font-medium">
                                {{ $source->last_synced_at ? $source->last_synced_at->diffForHumans() : 'Never synced' }}
                            </td>
                            <td class="px-5 py-4 text-right space-x-1">
                                <button onclick="testSourceConnection({{ $source->id }})" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-[11px] font-bold transition">
                                    <i class="fa-solid fa-bolt mr-1"></i> Test
                                </button>
                                <form action="{{ route('lead-sources.destroy', $source->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this lead source integration?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-[11px] font-bold transition">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                                    <i class="fa-solid fa-plug"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-800">No Lead Sources Configured Yet</p>
                                <p class="text-xs text-slate-500 mt-1">Click "Add Lead Source" above to start capturing leads automatically.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Lead Source Modal -->
<div id="addSourceModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 w-full max-w-lg rounded-2xl shadow-2xl p-6 relative space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-plug"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">Add Lead Source Integration</h3>
            </div>
            <button onclick="closeAddSourceModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">&times;</button>
        </div>

        <form action="{{ route('lead-sources.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block text-slate-700 font-bold mb-1">Source Label Name *</label>
                <input type="text" name="name" required placeholder="e.g. Meta Main Campaign, 99acres Portal" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 font-medium focus:outline-none focus:border-indigo-600">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Integration Provider *</label>
                    <select name="type" id="modalSourceType" onchange="updateFormFields(this.value)" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                        @foreach($supportedTypes as $typeKey => $typeInfo)
                            <option value="{{ $typeKey }}">{{ $typeInfo['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">Default Project Mapping</label>
                    <select name="default_project_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
                        <option value="">Auto-Detect / Select Default</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Dynamic Credentials Container -->
            <div id="credentialsContainer" class="space-y-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider mb-2">Provider Credentials</div>
                
                <div class="cred-field field-meta">
                    <label class="block text-slate-700 font-bold mb-1">Facebook Page ID</label>
                    <input type="text" name="credentials[page_id]" placeholder="e.g. 109823487123" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-900 focus:outline-none">
                </div>

                <div class="cred-field field-meta">
                    <label class="block text-slate-700 font-bold mb-1">System User Access Token</label>
                    <input type="password" name="credentials[access_token]" placeholder="EAAG..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-900 focus:outline-none">
                </div>

                <div class="cred-field field-google hidden">
                    <label class="block text-slate-700 font-bold mb-1">Google Webhook Secret Key</label>
                    <input type="text" name="credentials[google_secret_key]" placeholder="Google Ads webhook secret key" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-900 focus:outline-none">
                </div>

                <div class="cred-field field-99acres hidden">
                    <label class="block text-slate-700 font-bold mb-1">99acres API Key</label>
                    <input type="text" name="credentials[api_key]" placeholder="99acres Lead API Key" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-900 focus:outline-none">
                </div>

                <div class="cred-field field-magicbricks hidden">
                    <label class="block text-slate-700 font-bold mb-1">MagicBricks API Key</label>
                    <input type="text" name="credentials[api_key]" placeholder="MagicBricks Lead Key" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-900 focus:outline-none">
                </div>

                <div class="cred-field field-housing hidden">
                    <label class="block text-slate-700 font-bold mb-1">Housing.com Auth Token</label>
                    <input type="text" name="credentials[auth_token]" placeholder="Housing Auth Token" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-900 focus:outline-none">
                </div>

                <div class="cred-field field-website hidden">
                    <label class="block text-slate-700 font-bold mb-1">Secret Verification Token (Optional)</label>
                    <input type="text" name="credentials[secret_key]" placeholder="Custom header or secret key" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-900 focus:outline-none">
                </div>
            </div>

            <div class="flex items-center space-x-2 pt-1">
                <input type="checkbox" name="is_active" value="1" id="isActive" checked class="rounded border-slate-300 text-indigo-600 focus:ring-0">
                <label for="isActive" class="text-xs text-slate-700 font-medium">Enable real-time webhook lead ingestion</label>
            </div>

            <div class="flex items-center justify-end space-x-2 border-t border-slate-100 pt-3">
                <button type="button" onclick="closeAddSourceModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm">Save & Connect Source</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddSourceModal() {
    document.getElementById('addSourceModal').classList.remove('hidden');
}

function closeAddSourceModal() {
    document.getElementById('addSourceModal').classList.add('hidden');
}

function openConfigureModal(type) {
    document.getElementById('modalSourceType').value = type;
    updateFormFields(type);
    openAddSourceModal();
}

function updateFormFields(selectedType) {
    document.querySelectorAll('.cred-field').forEach(el => el.classList.add('hidden'));
    const activeFields = document.querySelectorAll('.field-' + selectedType);
    if (activeFields.length > 0) {
        activeFields.forEach(el => el.classList.remove('hidden'));
    } else {
        document.querySelectorAll('.field-website').forEach(el => el.classList.remove('hidden'));
    }
}

function copyWebhookUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('âœ… Webhook URL copied to clipboard!');
    }).catch(err => {
        alert('Failed to copy URL: ' + err);
    });
}

function testSourceConnection(sourceId) {
    fetch(`/lead-sources/${sourceId}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('âœ… Connection Test Successful: ' + data.message);
            window.location.reload();
        } else {
            alert('âŒ Connection Test Failed: ' + data.message);
        }
    })
    .catch(err => {
        alert('Error testing connection: ' + err);
    });
}
</script>
@endsection
