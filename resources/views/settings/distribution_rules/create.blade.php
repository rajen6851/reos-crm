@extends('layouts.reos')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-5xl mx-auto">

    <!-- Page Header -->
    <div class="mb-8">
        <a href="{{ route('distribution-rules.index') }}" class="text-sm font-medium text-indigo-500 hover:text-indigo-600 mb-2 inline-flex items-center transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Rules
        </a>
        <h1 class="text-2xl md:text-3xl text-slate-800 font-bold tracking-tight">Create Distribution Rule</h1>
    </div>

    @if($errors->any())
        <div class="mb-6 px-4 py-3 rounded-lg bg-rose-50 border border-rose-200 flex items-start shadow-sm">
            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-rose-100 text-rose-600 mr-3 mt-0.5">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div>
                <h3 class="text-sm font-medium text-rose-800 mb-1">Please fix the following errors:</h3>
                <ul class="text-sm text-rose-700 list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('distribution-rules.store') }}" method="POST" id="ruleForm" class="space-y-6">
        @csrf
        
        <!-- Card 1: Basic Info -->
        <div class="bg-white shadow-lg rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h2 class="text-lg font-semibold text-slate-800 flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    Basic Information
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="col-span-1 md:col-span-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Rule Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" class="form-input w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required value="{{ old('name') }}" placeholder="e.g. Meta Ads High Priority">
                    </div>
                    <div class="col-span-1 md:col-span-3">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Priority <span class="text-rose-500">*</span></label>
                        <input type="number" name="priority" class="form-input w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" min="1" required value="{{ old('priority', 5) }}">
                        <p class="text-xs text-slate-500 mt-1">1 is highest priority</p>
                    </div>
                    <div class="col-span-1 md:col-span-3">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="is_active" class="form-select w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Conditions -->
        <div class="bg-white shadow-lg rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h2 class="text-lg font-semibold text-slate-800 flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mr-3">
                        <i class="fas fa-filter"></i>
                    </div>
                    Conditions (Optional)
                </h2>
                <p class="text-sm text-slate-500 mt-1 ml-11">Leave blank to apply this rule to all incoming leads.</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Project</label>
                        <select name="project_id" class="form-select w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="">-- All Projects --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lead Source</label>
                        <select name="lead_source_id" class="form-select w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="">-- All Sources --</option>
                            @foreach($sources as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Distribution Settings -->
        <div class="bg-white shadow-lg rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h2 class="text-lg font-semibold text-slate-800 flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center mr-3">
                        <i class="fas fa-cogs"></i>
                    </div>
                    Distribution Settings
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Distribution Method <span class="text-rose-500">*</span></label>
                        <select name="distribution_method" id="methodSelect" class="form-select w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="round_robin">Round Robin</option>
                            <option value="percentage">Percentage (Weighted)</option>
                            <option value="fixed_quantity">Fixed Quantity</option>
                            <option value="performance">Performance Based (AI Recommended)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fallback Behavior <span class="text-rose-500">*</span></label>
                        <select name="fallback_behavior" class="form-select w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="redistribute">Automatically Redistribute</option>
                            <option value="skip">Skip to next member</option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">If a member is inactive or on leave.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Team Members -->
        <div class="bg-white shadow-lg rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-slate-800 flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mr-3">
                        <i class="fas fa-users"></i>
                    </div>
                    Team Members
                </h2>
                <button type="button" id="addMemberBtn" class="btn-sm bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg font-medium shadow-sm transition-all text-sm">
                    <i class="fas fa-plus mr-1"></i> Add Member
                </button>
            </div>
            <div class="p-6 bg-slate-50">
                <div id="membersContainer" class="space-y-3">
                    <div class="member-row bg-white p-4 rounded-lg border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 items-center">
                        <div class="w-full md:w-5/12">
                            <select name="members[0][user_id]" class="form-select w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select User...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->name ?? 'Sales' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-5/12 relative">
                            <input type="number" name="members[0][allocation_value]" class="form-input w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 allocation-input pl-4 pr-10" placeholder="Allocation Value" step="0.01" min="0">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-sm font-medium method-unit">%</div>
                        </div>
                        <div class="w-full md:w-2/12 flex justify-end">
                            <button type="button" class="remove-btn text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 p-2 rounded-lg transition-colors" title="Remove Member">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit actions -->
        <div class="flex justify-end space-x-3 pt-4">
            <a href="{{ route('distribution-rules.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-medium transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm transition-all flex items-center">
                <i class="fas fa-save mr-2"></i> Save Rule
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('membersContainer');
    const addBtn = document.getElementById('addMemberBtn');
    const methodSelect = document.getElementById('methodSelect');
    let memberIndex = 1;

    // Change unit label based on distribution method
    function updateUnits() {
        const method = methodSelect.value;
        const units = document.querySelectorAll('.method-unit');
        const inputs = document.querySelectorAll('.allocation-input');
        
        if(method === 'percentage') {
            units.forEach(u => u.textContent = '%');
            inputs.forEach(i => {
                i.style.display = 'block';
                i.required = true;
                i.placeholder = 'Percentage %';
            });
            document.querySelectorAll('.relative').forEach(r => r.style.display = 'block');
        } else if(method === 'fixed_quantity') {
            units.forEach(u => u.textContent = 'Lds');
            inputs.forEach(i => {
                i.style.display = 'block';
                i.required = true;
                i.placeholder = 'Qty (Leads)';
            });
            document.querySelectorAll('.relative').forEach(r => r.style.display = 'block');
        } else {
            // Round robin or performance -> allocation not strictly required by user input in UI
            inputs.forEach(i => {
                i.style.display = 'none';
                i.required = false;
            });
            document.querySelectorAll('.relative').forEach(r => r.style.display = 'none');
        }
    }

    methodSelect.addEventListener('change', updateUnits);
    updateUnits(); // initial call

    addBtn.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'member-row bg-white p-4 rounded-lg border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 items-center animate-fade-in-up';
        
        row.innerHTML = `
            <div class="w-full md:w-5/12">
                <select name="members[${memberIndex}][user_id]" class="form-select w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">Select User...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->name ?? 'Sales' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-5/12 relative">
                <input type="number" name="members[${memberIndex}][allocation_value]" class="form-input w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 allocation-input pl-4 pr-10" placeholder="Allocation Value" step="0.01" min="0">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-sm font-medium method-unit">%</div>
            </div>
            <div class="w-full md:w-2/12 flex justify-end">
                <button type="button" class="remove-btn text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 p-2 rounded-lg transition-colors" title="Remove Member">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        memberIndex++;
        updateUnits();
    });

    container.addEventListener('click', function(e) {
        if(e.target.closest('.remove-btn')) {
            const row = e.target.closest('.member-row');
            if(document.querySelectorAll('.member-row').length > 1) {
                row.remove();
            } else {
                alert('At least one member is required.');
            }
        }
    });
});
</script>

<style>
.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
/* Ensure form inputs have tailwind base styles applied if plugins aren't active */
.form-input, .form-select {
    border-color: #cbd5e1;
}
.form-input:focus, .form-select:focus {
    outline: none;
    box-shadow: 0 0 0 1px #6366f1;
    border-color: #6366f1;
}
</style>
@endsection
