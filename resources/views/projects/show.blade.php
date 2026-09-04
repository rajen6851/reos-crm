@extends('layouts.reos')

@section('title', "{$project->name} – Inventory & Towers Engine – REOS")

@section('content')
<div class="space-y-6">
    <!-- Header Navigation & Project Specs Banner -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-200/80 shadow-2xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <div class="flex items-center space-x-2.5">
                    <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600">← Back to Projects Directory</a>
                    <span class="text-slate-300">•</span>
                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-mono font-bold text-[11px]">{{ $project->code }}</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ $project->name }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Location: <strong class="text-slate-800">{{ $project->city }}</strong> | RERA Registration: <span class="font-mono text-slate-800 font-bold">{{ $project->rera_number }}</span></p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <div class="text-right text-xs font-mono pr-2">
                    <div class="font-bold text-slate-900">{{ $project->buildings->count() }} Towers / {{ $project->units->count() }} Units</div>
                    <div class="text-[11px] text-emerald-600 font-bold">{{ $project->units->where('status', 'available')->count() }} Available</div>
                </div>

                <button onclick="document.getElementById('sharePublicLinkModal').classList.remove('hidden')" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-link mr-1"></i><span>Share Public Link</span>
                </button>

                @can('manage-projects')
                <button onclick="document.getElementById('addTowerModal').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-building mr-1"></i><span>+ Add Tower</span>
                </button>

                <button onclick="openAddUnitModal({{ json_encode($project) }})" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus mr-1"></i><span>+ Add Unit</span>
                </button>
                @endcan
            </div>
        </div>

        <!-- Inventory Summary Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-2 text-xs">
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                <span class="text-slate-400 font-medium">Total Inventory</span>
                <div class="text-lg font-bold font-mono text-slate-900 mt-0.5">{{ $project->units->count() }} Units</div>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200/80">
                <span class="text-emerald-700 font-medium">Available Units</span>
                <div class="text-lg font-bold font-mono text-emerald-800 mt-0.5">{{ $project->units->where('status', 'available')->count() }} Units</div>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/80">
                <span class="text-amber-700 font-medium">Hold / Reserved</span>
                <div class="text-lg font-bold font-mono text-amber-800 mt-0.5">{{ $project->units->where('status', 'hold')->count() }} Units</div>
            </div>
            <div class="p-3 bg-rose-50 rounded-xl border border-rose-200/80">
                <span class="text-rose-700 font-medium">Booked / Sold</span>
                <div class="text-lg font-bold font-mono text-rose-800 mt-0.5">{{ $project->units->where('status', 'booked')->count() }} Units</div>
            </div>
        </div>
    </div>

    <!-- Tower-by-Tower Inventory Matrix -->
    <div class="space-y-6">
        @forelse($project->buildings as $bldg)
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-building text-indigo-600 text-lg"></i>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ $bldg->name }} ({{ $bldg->code }})</h2>
                        <div class="text-xs text-slate-500 font-mono">{{ $bldg->total_floors }} Floors • {{ $bldg->units->count() }} Total Units</div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $bldg->units->where('status', 'available')->count() }} Available
                    </span>
                </div>
            </div>

            <!-- Units Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @forelse($bldg->units as $unit)
                @php
                    $statusStyle = match($unit->status) {
                        'available' => 'bg-emerald-50 border-emerald-300 text-emerald-950',
                        'hold' => 'bg-amber-50 border-amber-300 text-amber-950',
                        'booked' => 'bg-rose-50 border-rose-300 text-rose-950',
                        default => 'bg-slate-50 border-slate-200 text-slate-900'
                    };
                @endphp
                <div class="p-3.5 rounded-2xl border transition-all duration-150 space-y-2 bg-white shadow-2xs {{ $statusStyle }}">
                    <div class="flex justify-between items-start">
                        <div class="text-xs font-bold text-slate-900">Unit {{ $unit->unit_number }}</div>
                        <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-700">{{ $unit->unit_type }}</span>
                    </div>

                    <div class="text-xs font-mono font-bold text-emerald-700">₹{{ number_format($unit->final_price ?? $unit->price ?? 0) }}</div>
                    <div class="text-[10px] text-slate-500 font-mono">{{ $unit->carpet_area }} sqft</div>

                    @can('manage-projects')
                    <div class="pt-1.5 flex items-center justify-between gap-1 border-t border-slate-100">
                        <form action="{{ route('units.update-status', $unit->id) }}" method="POST" class="flex-1">
                            @csrf
                            <select name="status" onchange="this.form.submit()" class="w-full text-[10px] bg-slate-50 border border-slate-200 rounded p-1 font-semibold text-slate-800 focus:outline-none">
                                <option value="available" {{ $unit->status === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="hold" {{ $unit->status === 'hold' ? 'selected' : '' }}>Hold</option>
                                <option value="booked" {{ $unit->status === 'booked' ? 'selected' : '' }}>Booked</option>
                            </select>
                        </form>
                        
                        <button type="button" onclick="openEditUnitModal({{ json_encode($unit) }})" class="text-slate-500 hover:text-amber-600 text-xs p-0.5" title="Edit Unit Specs"><i class="fa-solid fa-pen-to-square"></i></button>

                        <form action="{{ route('units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('Delete Unit {{ $unit->unit_number }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-rose-600 text-xs p-0.5" title="Delete Unit"><i class="fa-solid fa-trash-can text-rose-500"></i></button>
                        </form>
                    </div>
                    @endcan
                </div>
                @empty
                <div class="col-span-full text-xs text-slate-400 italic py-3 bg-slate-50 rounded-xl text-center border border-dashed border-slate-200">
                    No units added in {{ $bldg->name }} yet. Click '+ Add Unit' above.
                </div>
                @endforelse
            </div>
        </div>
        @empty
        <div class="p-8 text-center bg-white rounded-3xl border border-slate-200 shadow-2xs space-y-3">
            <div class="text-3xl"><i class="fa-solid fa-building text-indigo-600"></i></div>
            <h3 class="text-base font-bold text-slate-800">No Towers Created Yet</h3>
            <p class="text-xs text-slate-500">Create your first building tower for {{ $project->name }} to manage floor inventory.</p>
            @can('manage-projects')
            <button onclick="document.getElementById('addTowerModal').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition inline-block">
                + Add Tower / Building
            </button>
            @endcan
        </div>
        @endforelse
    </div>

    <!-- Add Tower / Building Modal -->
    <div id="addTowerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-md p-6 rounded-2xl space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Add Tower / Building</h3>
                <button onclick="document.getElementById('addTowerModal').classList.add('hidden')" class="text-slate-400 font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('projects.store-building', $project->id) }}" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Tower Name *</label>
                        <input type="text" name="name" required placeholder="Tower A / Block 1" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Tower Code *</label>
                        <input type="text" name="code" required placeholder="TA" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 uppercase focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Total Floors *</label>
                    <input type="number" name="total_floors" required min="1" value="10" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 focus:outline-none focus:border-indigo-500 font-mono">
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('addTowerModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs">Add Tower</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Unit Modal -->
    <div id="addUnitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-lg p-6 rounded-2xl space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Add Inventory Unit</h3>
                <button onclick="document.getElementById('addUnitModal').classList.add('hidden')" class="text-slate-400 font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('projects.store-unit', $project->id) }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 mb-1 font-semibold">Select Tower / Building *</label>
                    <select name="building_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500">
                        @foreach($project->buildings as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Unit No. *</label>
                        <input type="text" name="unit_number" required placeholder="101" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Floor No.</label>
                        <input type="number" name="floor_number" value="1" min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Unit Type *</label>
                        <select name="unit_type" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500">
                            <option value="2BHK">2BHK</option>
                            <option value="3BHK">3BHK</option>
                            <option value="4BHK">4BHK / Villa</option>
                            <option value="Penthouse">Penthouse</option>
                            <option value="Plot">Plot</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Carpet Area (sqft) *</label>
                        <input type="number" name="carpet_area" required value="1250" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Base Price (₹) *</label>
                        <input type="number" name="base_price" required value="7500000" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Final Price (₹) *</label>
                        <input type="number" name="final_price" required value="8200000" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('addUnitModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs">Save Unit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Unit Modal -->
    <div id="editUnitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
        <div class="bg-white w-full max-w-lg p-6 rounded-2xl space-y-4 border border-slate-200 shadow-xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Edit Inventory Unit Specs</h3>
                <button onclick="document.getElementById('editUnitModal').classList.add('hidden')" class="text-slate-400 font-bold">✕</button>
            </div>

            <form id="editUnitForm" method="POST" action="" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Unit No. *</label>
                        <input type="text" id="edit_unit_number" name="unit_number" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Unit Type *</label>
                        <select id="edit_unit_type" name="unit_type" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500">
                            <option value="2BHK">2BHK</option>
                            <option value="3BHK">3BHK</option>
                            <option value="4BHK">4BHK / Villa</option>
                            <option value="Penthouse">Penthouse</option>
                            <option value="Plot">Plot</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Carpet Area (sqft) *</label>
                        <input type="number" id="edit_carpet_area" name="carpet_area" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Base Price (₹) *</label>
                        <input type="number" id="edit_base_price" name="base_price" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 font-semibold">Final Price (₹) *</label>
                        <input type="number" id="edit_final_price" name="final_price" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-900 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('editUnitModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-xs">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Share Public Link Modal -->
<div id="sharePublicLinkModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-2xs p-4">
    <div class="bg-white w-full max-w-md p-6 rounded-2xl space-y-4 border border-slate-200 shadow-xl">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-link text-indigo-600"></i>
                <h3 class="text-base font-bold text-slate-900">Share Public Project Showcase Link</h3>
            </div>
            <button onclick="document.getElementById('sharePublicLinkModal').classList.add('hidden')" class="text-slate-400 font-bold hover:text-slate-700">✕</button>
        </div>

        <p class="text-xs text-slate-500">
            This public link allows prospective buyers/customers to view basic project details, towers, configurations, and submit direct callback inquiries without logging in.
        </p>

        @php
            $publicUrl = route('projects.public', $project->id);
            if(auth()->user()->isBroker()) {
                $brokerObj = \App\Models\Broker::where('user_id', auth()->id())->first();
                if($brokerObj) {
                    $publicUrl .= '?ref=' . $brokerObj->id;
                }
            }
        @endphp

        <div class="space-y-2">
            <label class="block text-xs font-semibold text-slate-700">Public Link URL</label>
            <div class="flex items-center space-x-2">
                <input type="text" readonly id="publicProjectUrlInput" value="{{ $publicUrl }}" 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-mono focus:outline-none">
                <button onclick="copyProjectLinkModal()" class="px-3 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-2xs whitespace-nowrap transition cursor-pointer">
                    <span id="modalCopyBtnText">Copy</span>
                </button>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
            <a href="{{ $publicUrl }}" target="_blank" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-center font-bold text-xs rounded-xl transition">
                <i class="fa-solid fa-eye mr-1"></i>Preview Link
            </a>
            <button onclick="shareWhatsAppModal()" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-center font-bold text-xs rounded-xl transition shadow-2xs cursor-pointer">
                <i class="fa-brands fa-whatsapp text-white mr-1"></i>WhatsApp Share
            </button>
        </div>
    </div>
</div>

<script>
    function openAddUnitModal(project) {
        var buildings = project.buildings || [];
        if (buildings.length === 0) {
            alert('Please create a Tower first by clicking "+ Add Tower" before adding units.');
            return;
        }
        document.getElementById('addUnitModal').classList.remove('hidden');
    }

    function openEditUnitModal(unit) {
        document.getElementById('editUnitForm').action = "/units/" + unit.id;
        document.getElementById('edit_unit_number').value = unit.unit_number || '';
        document.getElementById('edit_unit_type').value = unit.unit_type || '2BHK';
        document.getElementById('edit_carpet_area').value = unit.carpet_area || '';
        document.getElementById('edit_base_price').value = unit.base_price || '';
        document.getElementById('edit_final_price').value = unit.final_price || unit.price || '';
        document.getElementById('editUnitModal').classList.remove('hidden');
    }

    function copyProjectLinkModal() {
        const input = document.getElementById('publicProjectUrlInput');
        navigator.clipboard.writeText(input.value).then(() => {
            const btnText = document.getElementById('modalCopyBtnText');
            btnText.innerText = 'Copied!';
            setTimeout(() => { btnText.innerText = 'Copy'; }, 2500);
        });
    }

    function shareWhatsAppModal() {
        const url = encodeURIComponent(document.getElementById('publicProjectUrlInput').value);
        const text = encodeURIComponent("Check out {{ $project->name }} in {{ $project->city }}!\nDetails & Pricing link: ");
        window.open(`https://api.whatsapp.com/send?text=${text}${url}`, '_blank');
    }
</script>
@endsection
