@extends('layouts.reos')

@section('title', 'Projects Directory')

@section('content')
<div class="space-y-5 max-w-7xl mx-auto pb-12">
    <!-- Header Banner -->
    <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#2563EB] transition">Home</a>
                <span>&gt;</span>
                <span class="text-slate-900 font-bold">Properties Directory</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Real Estate Projects & Inventory Directory</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Manage residential enclaves, commercial towers, floor maps, and unit availability matrices</p>
        </div>

        @can('manage-projects')
        <div>
            <button onclick="document.getElementById('createProjectModal').classList.remove('hidden')" class="px-4 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Create Project</span>
            </button>
        </div>
        @endcan
    </div>

    <!-- Pending Critical Approval Requests for Director / Founder / Main Owner -->
    @if(auth()->user()->isDirectorOrFounder() && isset($pendingProjectApprovals) && $pendingProjectApprovals->count() > 0)
    <div class="bg-amber-50/70 border border-amber-200 rounded-lg p-4 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-md bg-amber-100 border border-amber-300 text-amber-800 flex items-center justify-center text-sm font-bold shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-xs">Critical Approval Requests (Owner Verification Required)</h3>
                    <p class="text-xs text-slate-500">Admins have requested project deletion actions requiring your authorization.</p>
                </div>
            </div>
            <span class="px-2.5 py-0.5 bg-amber-200/80 text-amber-900 text-[11px] font-bold rounded-md border border-amber-300">
                {{ $pendingProjectApprovals->count() }} Pending Request{{ $pendingProjectApprovals->count() > 1 ? 's' : '' }}
            </span>
        </div>

        <div class="space-y-2">
            @foreach($pendingProjectApprovals as $approval)
            <div class="bg-white rounded-md p-3 border border-amber-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-start space-x-3">
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded border {{ $approval->action_badge }}">
                        {{ $approval->action_label }}
                    </span>
                    <div>
                        <div class="text-xs font-bold text-slate-900">
                            Target Project: <span class="text-rose-600 font-mono">{{ $approval->target_name }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            Requested by Admin: <strong class="text-slate-800">{{ $approval->requestedBy->name ?? 'Admin User' }}</strong>
                            • <span class="font-mono">{{ $approval->created_at->diffForHumans() }}</span>
                        </div>
                        @if($approval->reason)
                            <div class="text-[11px] text-amber-900 bg-amber-50 rounded p-2 mt-1.5 border border-amber-200">
                                💬 <em>"{{ $approval->reason }}"</em>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center space-x-2 shrink-0 self-end md:self-center">
                    <form action="{{ route('users.approvals.approve', $approval->id) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to APPROVE & DELETE this project permanently?')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-md shadow-2xs transition flex items-center space-x-1 cursor-pointer">
                            <i class="fa-solid fa-check text-xs"></i>
                            <span>Approve & Delete Project</span>
                        </button>
                    </form>

                    <form action="{{ route('users.approvals.reject', $approval->id) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to REJECT this deletion request?')" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-md shadow-2xs transition flex items-center space-x-1 cursor-pointer">
                            <i class="fa-solid fa-xmark text-xs"></i>
                            <span>Reject</span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Projects Grid -->
    @if($projects->isEmpty())
        <div class="p-8 text-center bg-white rounded-xl border border-slate-200/80 text-xs text-slate-500 font-medium shadow-xs">
            No real estate projects created yet. Click "+ Create Project" to add your first project.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($projects as $project)
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between space-y-3.5 p-4">
                <div class="space-y-3">
                    <!-- Project Banner Image -->
                    <div class="h-36 w-full overflow-hidden relative rounded-lg bg-slate-100 border border-slate-200/70">
                        @if($project->banner_image)
                            <img src="{{ $project->banner_image }}" alt="{{ $project->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-slate-900 flex items-center justify-center text-white font-black text-lg tracking-widest">
                                {{ strtoupper(substr($project->name, 0, 3)) }}
                            </div>
                        @endif
                        <div class="absolute top-2.5 left-2.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-white/90 backdrop-blur-xs text-slate-800 border border-white/50 shadow-xs">
                                {{ $project->project_type ?? 'Residential' }}
                            </span>
                        </div>
                        <div class="absolute top-2.5 right-2.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-900/80 backdrop-blur-xs text-emerald-400 border border-white/20">
                                {{ $project->code }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">{{ $project->name }}</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $project->city ?? 'Location N/A' }} • RERA: {{ $project->rera_number ?? 'Pending' }}</p>
                    </div>

                    <div class="flex items-center space-x-3 text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">
                        <span><i class="fa-solid fa-building text-indigo-500 mr-1.5"></i>{{ $project->buildings->count() }} Towers</span>
                        <span>•</span>
                        <span><i class="fa-solid fa-boxes-stacked text-emerald-500 mr-1.5"></i>{{ $project->units->count() }} Units</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="{{ route('projects.show', $project->id) }}" class="flex-1 px-3 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-center text-xs font-bold rounded-lg transition shadow-xs">
                        <i class="fa-solid fa-building mr-1.5 text-xs"></i>View Inventory
                    </a>

                    <a href="{{ route('projects.public', $project->id) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 rounded-md text-xs font-semibold transition flex items-center justify-center" title="Preview Public Link">
                        <i class="fa-solid fa-link mr-1 text-slate-400"></i>
                        <span>Showcase</span>
                    </a>

                    @can('manage-projects')
                    <!-- Options Menu Dropdown -->
                    <div class="relative">
                        <button type="button" onclick="event.stopPropagation(); toggleProjectMenu({{ $project->id }});" class="w-8 h-8 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition border border-slate-200 cursor-pointer active:scale-95" title="More Options">
                            <i class="fa-solid fa-ellipsis-vertical text-xs"></i>
                        </button>

                        <div id="projectMenu_{{ $project->id }}" class="hidden absolute right-0 bottom-10 w-36 bg-white rounded-md shadow-xl border border-slate-200 p-1 z-50 text-xs space-y-0.5">
                            <button type="button" onclick="event.stopPropagation(); openEditProjectModal({{ json_encode($project) }}); hideAllProjectMenus();" class="w-full text-left px-3 py-1.5 text-slate-700 hover:bg-slate-50 rounded font-medium flex items-center space-x-2 transition cursor-pointer">
                                <i class="fa-solid fa-pen-to-square text-slate-400 text-xs"></i>
                                <span>Edit</span>
                            </button>

                            <form method="POST" action="{{ route('projects.destroy', $project->id) }}" onsubmit="return confirm('Delete project {{ $project->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-left px-3 py-1.5 text-rose-600 hover:bg-rose-50 rounded font-medium flex items-center space-x-2 transition cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-rose-500 text-xs"></i>
                                    <span>Delete</span>
                                </button>
                            </form>

                            <a href="{{ route('projects.public', $project->id) }}" target="_blank" class="w-full text-left px-3 py-1.5 text-slate-700 hover:bg-slate-50 rounded font-medium flex items-center space-x-2 transition block">
                                <i class="fa-solid fa-eye text-slate-400 text-xs"></i>
                                <span>Preview</span>
                            </a>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
            @endforeach
        </div>
    @endif

    <!-- RIGHT SLIDE-OVER DRAWER PANEL 1: Create Real Estate Project -->
    <div id="createProjectModal" class="hidden fixed inset-0 z-50 overflow-hidden">
        <div onclick="document.getElementById('createProjectModal').classList.add('hidden')" class="absolute inset-0 bg-slate-900/50 backdrop-blur-2xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl z-50 flex flex-col justify-between transform transition-transform duration-300 ease-in-out border-l border-slate-200">
            <!-- Header -->
            <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-md bg-blue-50 border border-blue-200 text-[#2563EB] flex items-center justify-center font-bold text-sm shrink-0">
                        <i class="fa-solid fa-building-circle-check"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Create Real Estate Project</h3>
                        <p class="text-xs text-slate-500">Add new builder project & inventory specs</p>
                    </div>
                </div>
                <button onclick="document.getElementById('createProjectModal').classList.add('hidden')" class="w-7 h-7 rounded bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center font-bold text-xs transition cursor-pointer">✕</button>
            </div>

            <!-- Drawer Form Body -->
            <form id="createProjectForm" method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data" class="p-5 overflow-y-auto flex-1 space-y-4 text-xs">
                @csrf
                <div class="space-y-3">
                    <div class="text-[11px] font-bold uppercase text-[#2563EB] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-building text-xs"></i>
                        <span>1. Project Basic Details</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Project Name *</label>
                            <input type="text" name="name" required placeholder="Royal Palms Heights" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-bold focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Project Code *</label>
                            <input type="text" name="code" required placeholder="RPH" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono uppercase font-bold focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">City</label>
                            <input type="text" name="city" placeholder="Hyderabad" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-medium focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">RERA Number</label>
                            <input type="text" name="rera_number" placeholder="P02400001234" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                        </div>
                    </div>
                </div>

                <div class="p-3.5 rounded-md bg-slate-50 border border-slate-200 space-y-3">
                    <div class="text-[11px] font-bold uppercase text-slate-800 tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-layer-group text-xs text-slate-500"></i>
                        <span>2. Classification & Visibility</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-[10px] text-slate-600 uppercase mb-1">Project Type</label>
                            <select name="project_type" class="w-full bg-white border border-slate-300 rounded-md p-2 text-slate-900 font-semibold focus:outline-none focus:border-[#2563EB]">
                                <option value="residential">Residential Enclave</option>
                                <option value="commercial">Commercial Complex</option>
                                <option value="mixed">Mixed Development</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-[10px] text-slate-600 uppercase mb-1">Broker Visibility *</label>
                            <select name="visibility" class="w-full bg-white border border-slate-300 rounded-md p-2 text-slate-900 font-semibold focus:outline-none focus:border-[#2563EB]">
                                <option value="public">Public (Visible to Brokers)</option>
                                <option value="private">Private (Company Team Only)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="text-[11px] font-bold uppercase text-emerald-700 tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-image text-xs"></i>
                        <span>3. Project Banner Media</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Upload Banner Image (JPG, PNG, WebP)</label>
                        <input type="file" name="banner_image" accept="image/*" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                    </div>
                </div>
            </form>

            <!-- Footer Actions -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('createProjectModal').classList.add('hidden')" class="px-4 py-1.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-md hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                <button type="submit" form="createProjectForm" class="px-4 py-1.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold rounded-md shadow-xs transition cursor-pointer">Create Project</button>
            </div>
        </div>
    </div>

    <!-- RIGHT SLIDE-OVER DRAWER PANEL 2: Edit Project Details -->
    <div id="editProjectModal" class="hidden fixed inset-0 z-50 overflow-hidden">
        <div onclick="document.getElementById('editProjectModal').classList.add('hidden')" class="absolute inset-0 bg-slate-900/50 backdrop-blur-2xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl z-50 flex flex-col justify-between transform transition-transform duration-300 ease-in-out border-l border-slate-200">
            <!-- Header -->
            <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-md bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm shrink-0">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Edit Project Details</h3>
                        <p class="text-xs text-slate-500">Update project specifications & banner media</p>
                    </div>
                </div>
                <button onclick="document.getElementById('editProjectModal').classList.add('hidden')" class="w-7 h-7 rounded bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center font-bold text-xs transition cursor-pointer">✕</button>
            </div>

            <!-- Drawer Form Body -->
            <form id="editProjectForm" method="POST" action="" enctype="multipart/form-data" class="p-5 overflow-y-auto flex-1 space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="space-y-3">
                    <div class="text-[11px] font-bold uppercase text-[#2563EB] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-building text-xs"></i>
                        <span>1. Project Basic Details</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Project Name *</label>
                            <input type="text" id="edit_proj_name" name="name" required class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-bold focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Project Code *</label>
                            <input type="text" id="edit_proj_code" name="code" required class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono uppercase font-bold focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">City</label>
                            <input type="text" id="edit_proj_city" name="city" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-medium focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">RERA Number</label>
                            <input type="text" id="edit_proj_rera" name="rera_number" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 font-mono focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                        </div>
                    </div>
                </div>

                <div class="p-3.5 rounded-md bg-slate-50 border border-slate-200 space-y-3">
                    <div class="text-[11px] font-bold uppercase text-slate-800 tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-layer-group text-xs text-slate-500"></i>
                        <span>2. Classification & Visibility</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-[10px] text-slate-600 uppercase mb-1">Project Type</label>
                            <select id="edit_proj_type" name="project_type" class="w-full bg-white border border-slate-300 rounded-md p-2 text-slate-900 font-semibold focus:outline-none focus:border-[#2563EB]">
                                <option value="residential">Residential Enclave</option>
                                <option value="commercial">Commercial Complex</option>
                                <option value="mixed">Mixed Development</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-[10px] text-slate-600 uppercase mb-1">Broker Visibility *</label>
                            <select id="edit_proj_visibility" name="visibility" class="w-full bg-white border border-slate-300 rounded-md p-2 text-slate-900 font-semibold focus:outline-none focus:border-[#2563EB]">
                                <option value="public">Public (Visible to Brokers)</option>
                                <option value="private">Private (Company Team Only)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="text-[11px] font-bold uppercase text-emerald-700 tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-image text-xs"></i>
                        <span>3. Project Banner Media</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Upload New Banner Image</label>
                        <input type="file" name="banner_image" accept="image/*" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2 text-slate-900 focus:outline-none focus:border-[#2563EB] focus:bg-white transition">
                    </div>

                    <div id="edit_banner_preview_box" class="hidden space-y-1">
                        <span class="text-[10px] text-slate-500 font-bold uppercase">Current Banner Image</span>
                        <img id="edit_banner_preview_img" src="" class="h-28 w-full object-cover rounded-md border border-slate-200">
                    </div>
                </div>
            </form>

            <!-- Footer Actions -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('editProjectModal').classList.add('hidden')" class="px-4 py-1.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-md hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                <button type="submit" form="editProjectForm" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-md shadow-xs transition cursor-pointer">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleProjectMenu(id) {
        const targetMenu = document.getElementById('projectMenu_' + id);
        if (!targetMenu) return;
        
        const isCurrentlyHidden = targetMenu.classList.contains('hidden');
        hideAllProjectMenus();
        
        if (isCurrentlyHidden) {
            targetMenu.classList.remove('hidden');
        }
    }

    function hideAllProjectMenus() {
        const allMenus = document.querySelectorAll('[id^="projectMenu_"]');
        allMenus.forEach(menu => {
            menu.classList.add('hidden');
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="projectMenu_"]') && !e.target.closest('button[onclick*="toggleProjectMenu"]')) {
            hideAllProjectMenus();
        }
    });

    function openEditProjectModal(project) {
        document.getElementById('editProjectForm').action = "/projects/" + project.id;
        document.getElementById('edit_proj_name').value = project.name || '';
        document.getElementById('edit_proj_code').value = project.code || '';
        document.getElementById('edit_proj_city').value = project.city || '';
        document.getElementById('edit_proj_rera').value = project.rera_number || '';
        document.getElementById('edit_proj_type').value = project.project_type || 'residential';
        document.getElementById('edit_proj_visibility').value = project.visibility || 'public';

        if (project.banner_image) {
            document.getElementById('edit_banner_preview_img').src = project.banner_image;
            document.getElementById('edit_banner_preview_box').classList.remove('hidden');
        } else {
            document.getElementById('edit_banner_preview_box').classList.add('hidden');
        }

        document.getElementById('editProjectModal').classList.remove('hidden');
    }
</script>
@endsection
