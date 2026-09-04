@extends('layouts.reos')

@section('title', 'Projects & Property Inventory – REOS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Header Banner -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-[#E2E8F0] shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Projects Directory</span>
            </div>
            <h1 class="page-heading text-2xl font-extrabold text-[#0F172A]">Real Estate Projects & Inventory Directory</h1>
            <p class="body-text text-xs text-[#64748B] mt-0.5">Manage residential enclaves, commercial towers, floor maps, and unit availability matrices</p>
        </div>

        @can('manage-projects')
        <div>
            <button onclick="document.getElementById('createProjectModal').classList.remove('hidden')" class="px-5 py-3 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-white text-xs"></i>
                <span>+ Create Project</span>
            </button>
        </div>
        @endcan
    </div>

    <!-- Projects Grid -->
    @if($projects->isEmpty())
        <div class="p-8 text-center bg-white rounded-3xl border border-[#E2E8F0] text-xs text-slate-500 font-medium">
            No real estate projects created yet. Click "+ Create Project" to add your first project.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($projects as $project)
            <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-2xs hover:shadow-md transition overflow-hidden flex flex-col justify-between space-y-4 p-5">
                <div class="space-y-3">
                    <!-- Project Banner Image (Rendered Image Container) -->
                    <div class="h-36 w-full overflow-hidden relative rounded-2xl bg-slate-100 border border-slate-200">
                        @if($project->banner_image)
                            <img src="{{ $project->banner_image }}" alt="{{ $project->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-linear-to-br from-indigo-900 via-slate-800 to-slate-900 flex items-center justify-center text-white font-extrabold text-lg tracking-wider">
                                {{ strtoupper(substr($project->name, 0, 3)) }}
                            </div>
                        @endif
                        <div class="absolute top-2 left-2">
                            <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase bg-white/90 backdrop-blur-xs text-[#4F46E5] border border-white/40 shadow-2xs">
                                {{ $project->project_type ?? 'Residential' }}
                            </span>
                        </div>
                        <div class="absolute top-2 right-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-900/80 backdrop-blur-xs text-emerald-400 border border-white/20">
                                {{ $project->code }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-[#0F172A]">{{ $project->name }}</h3>
                        <p class="text-xs text-[#64748B] font-medium">{{ $project->city ?? 'Location N/A' }} • RERA: {{ $project->rera_number ?? 'Pending' }}</p>
                    </div>

                    <div class="flex items-center space-x-3 text-xs text-[#64748B] font-medium pt-1 border-t border-[#E2E8F0]">
                        <span><i class="fa-solid fa-building text-[#4F46E5] mr-1"></i>{{ $project->buildings->count() }} Towers</span>
                        <span>•</span>
                        <span><i class="fa-solid fa-boxes-stacked text-[#4F46E5] mr-1"></i>{{ $project->units->count() }} Units</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-[#E2E8F0] flex items-center justify-between gap-2">
                    <a href="{{ route('projects.show', $project->id) }}" class="flex-1 px-3 py-2 bg-[#4F46E5] hover:bg-[#4338CA] text-white text-center text-xs btn-text rounded-xl transition shadow-2xs">
                        <i class="fa-solid fa-building mr-1"></i>View Inventory
                    </a>

                    <a href="{{ route('projects.public', $project->id) }}" target="_blank" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-[#0F172A] border border-[#E2E8F0] rounded-xl text-xs font-bold transition flex items-center justify-center" title="Preview Public Link for Buyers">
                        <i class="fa-solid fa-link mr-1"></i>
                        <span>Showcase</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                    </a>

                    @can('manage-projects')
                    <!-- Red 3-Dots Vertical Button & Floating Options Menu -->
                    <div class="relative">
                        <button type="button" onclick="event.stopPropagation(); toggleProjectMenu({{ $project->id }});" class="w-8 h-9 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white flex items-center justify-center transition shadow-md cursor-pointer active:scale-95" title="More Options">
                            <i class="fa-solid fa-ellipsis-vertical text-base pointer-events-none"></i>
                        </button>

                        <div id="projectMenu_{{ $project->id }}" class="hidden absolute right-0 bottom-11 w-36 bg-white rounded-2xl shadow-2xl border border-slate-200 p-2 z-50 text-xs space-y-1">
                            <!-- Option 1: Edit -->
                            <button type="button" onclick="event.stopPropagation(); openEditProjectModal({{ json_encode($project) }}); hideAllProjectMenus();" class="w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-100 rounded-xl font-bold flex items-center space-x-2 transition cursor-pointer">
                                <i class="fa-solid fa-pen-to-square text-slate-500 text-xs"></i>
                                <span>Edit</span>
                            </button>

                            <!-- Option 2: Delete -->
                            <form method="POST" action="{{ route('projects.destroy', $project->id) }}" onsubmit="return confirm('Delete project {{ $project->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-left px-3 py-2 text-rose-600 hover:bg-rose-50 rounded-xl font-bold flex items-center space-x-2 transition cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-rose-500 text-xs"></i>
                                    <span>Delete</span>
                                </button>
                            </form>

                            <!-- Option 3: Preview / Showcase -->
                            <a href="{{ route('projects.public', $project->id) }}" target="_blank" class="w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-100 rounded-xl font-bold flex items-center space-x-2 transition block">
                                <i class="fa-solid fa-eye text-slate-500 text-xs"></i>
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
        <!-- Backdrop Blur -->
        <div onclick="document.getElementById('createProjectModal').classList.add('hidden')" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl z-50 flex flex-col justify-between transform transition-transform duration-300 ease-in-out border-l border-[#E2E8F0]">
            <!-- Header -->
            <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-200 text-[#DC2626] flex items-center justify-center font-extrabold text-sm shrink-0">
                        <i class="fa-solid fa-building-circle-check"></i>
                    </div>
                    <div>
                        <h3 class="section-heading text-lg">Create Real Estate Project</h3>
                        <p class="body-text text-xs text-[#64748B]">Add new builder project & inventory specs</p>
                    </div>
                </div>
                <button onclick="document.getElementById('createProjectModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center font-bold text-sm transition cursor-pointer">✕</button>
            </div>

            <!-- Drawer Form Body (Scrollable) -->
            <form id="createProjectForm" method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data" class="p-6 overflow-y-auto flex-1 space-y-5 text-xs">
                @csrf

                <!-- Section 1: Basic Project Info -->
                <div class="space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#4F46E5] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-building text-xs"></i>
                        <span>1. Project Basic Details</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Project Name *</label>
                            <input type="text" name="name" required placeholder="Royal Palms Heights" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-extrabold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Project Code *</label>
                            <input type="text" name="code" required placeholder="RPH" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-mono uppercase focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">City</label>
                            <input type="text" name="city" placeholder="Hyderabad" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">RERA Number</label>
                            <input type="text" name="rera_number" placeholder="P02400001234" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Visibility & Type -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#0F172A] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-layer-group text-xs text-slate-500"></i>
                        <span>2. Classification & Partner Visibility</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Project Type</label>
                            <select name="project_type" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]">
                                <option value="residential">Residential Enclave</option>
                                <option value="commercial">Commercial Complex</option>
                                <option value="mixed">Mixed Development</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Broker Visibility *</label>
                            <select name="visibility" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]">
                                <option value="public">Public (Visible to Brokers)</option>
                                <option value="private">Private (Company Team Only)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Media & Showcase -->
                <div class="space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#059669] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-image text-xs"></i>
                        <span>3. Project Banner Media</span>
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Upload Banner Image (JPG, PNG, WebP)</label>
                        <input type="file" name="banner_image" accept="image/*" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                    </div>
                </div>
            </form>

            <!-- Footer Actions -->
            <div class="p-6 border-t border-[#E2E8F0] bg-slate-50/80 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('createProjectModal').classList.add('hidden')" class="px-5 py-2.5 bg-white border border-[#E2E8F0] text-[#0F172A] btn-text rounded-xl hover:bg-slate-50 transition cursor-pointer">Cancel</button>
                <button type="submit" form="createProjectForm" class="px-6 py-2.5 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text rounded-xl shadow-xs transition cursor-pointer">Create Project</button>
            </div>
        </div>
    </div>

    <!-- RIGHT SLIDE-OVER DRAWER PANEL 2: Edit Project Details -->
    <div id="editProjectModal" class="hidden fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop Blur -->
        <div onclick="document.getElementById('editProjectModal').classList.add('hidden')" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl z-50 flex flex-col justify-between transform transition-transform duration-300 ease-in-out border-l border-[#E2E8F0]">
            <!-- Header -->
            <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-200 text-[#4F46E5] flex items-center justify-center font-extrabold text-sm shrink-0">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="section-heading text-lg">Edit Project Details</h3>
                        <p class="body-text text-xs text-[#64748B]">Update project specifications & banner media</p>
                    </div>
                </div>
                <button onclick="document.getElementById('editProjectModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center font-bold text-sm transition cursor-pointer">✕</button>
            </div>

            <!-- Drawer Form Body (Scrollable) -->
            <form id="editProjectForm" method="POST" action="" enctype="multipart/form-data" class="p-6 overflow-y-auto flex-1 space-y-5 text-xs">
                @csrf
                @method('PUT')

                <!-- Section 1: Basic Info -->
                <div class="space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#4F46E5] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-building text-xs"></i>
                        <span>1. Project Basic Details</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Project Name *</label>
                            <input type="text" id="edit_proj_name" name="name" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-extrabold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Project Code *</label>
                            <input type="text" id="edit_proj_code" name="code" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-mono uppercase focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">City</label>
                            <input type="text" id="edit_proj_city" name="city" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">RERA Number</label>
                            <input type="text" id="edit_proj_rera" name="rera_number" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Visibility & Type -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#0F172A] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-layer-group text-xs text-slate-500"></i>
                        <span>2. Classification & Partner Visibility</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Project Type</label>
                            <select id="edit_proj_type" name="project_type" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]">
                                <option value="residential">Residential Enclave</option>
                                <option value="commercial">Commercial Complex</option>
                                <option value="mixed">Mixed Development</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Broker Visibility *</label>
                            <select id="edit_proj_visibility" name="visibility" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]">
                                <option value="public">Public (Visible to Brokers)</option>
                                <option value="private">Private (Company Team Only)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Media & Showcase -->
                <div class="space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#059669] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-image text-xs"></i>
                        <span>3. Project Banner Media</span>
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Upload New Banner Image (JPG, PNG, WebP)</label>
                        <input type="file" name="banner_image" accept="image/*" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                    </div>

                    <!-- Image Thumbnail Preview -->
                    <div id="edit_banner_preview_box" class="hidden space-y-1">
                        <span class="text-[10px] text-slate-500 font-bold uppercase">Current Banner Image</span>
                        <img id="edit_banner_preview_img" src="" class="h-28 w-full object-cover rounded-xl border border-slate-200 shadow-2xs">
                    </div>
                </div>
            </form>

            <!-- Footer Actions -->
            <div class="p-6 border-t border-[#E2E8F0] bg-slate-50/80 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('editProjectModal').classList.add('hidden')" class="px-5 py-2.5 bg-white border border-[#E2E8F0] text-[#0F172A] btn-text rounded-xl hover:bg-slate-50 transition cursor-pointer">Cancel</button>
                <button type="submit" form="editProjectForm" class="px-6 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text rounded-xl shadow-xs transition cursor-pointer">Save Changes</button>
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
