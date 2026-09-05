<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectFloor;
use App\Models\Unit;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isBroker()) {
            $broker = \App\Models\Broker::where('user_id', $user->id)->first() ?? \App\Models\Broker::where('email', $user->email)->first();
            $projects = Project::withoutGlobalScopes()
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->where('visibility', 'public')->orWhereNull('visibility');
                })
                ->with(['company', 'buildings.floors', 'units' => function ($uq) {
                    $uq->withoutGlobalScopes();
                }])
                ->latest()
                ->get();

            return view('projects.index', compact('projects', 'broker'));
        }

        if ($user->isSaaSFounder()) {
            $companies = \App\Models\Company::with(['projects' => function ($q) {
                $q->withoutGlobalScopes()->with(['buildings.floors', 'units' => function ($uq) {
                    $uq->withoutGlobalScopes();
                }]);
            }])->get();

            $projects = Project::withoutGlobalScopes()->with(['company', 'buildings.floors', 'units' => function ($uq) {
                $uq->withoutGlobalScopes();
            }])->latest()->get();

            return view('projects.index', compact('projects', 'companies'));
        }

        $projects = Project::with(['buildings.floors', 'units'])->latest()->get();
        return view('projects.index', compact('projects'));
    }

    public function store(Request $request, StorageService $storageService)
    {
        Gate::authorize('manage-projects');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'city' => 'nullable|string|max:100',
            'rera_number' => 'nullable|string|max:100',
            'project_type' => 'required|string',
            'visibility' => 'nullable|in:public,private',
            'banner_image' => 'nullable|file|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        $bannerPath = '/uploads/projects/default_project.jpg';
        if ($request->hasFile('banner_image')) {
            $bannerPath = $storageService->uploadToPublic($request->file('banner_image'), 'projects');
        }

        $project = Project::create([
            'company_id' => Auth::user()->company_id ?? 1,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'city' => $validated['city'] ?? 'Hyderabad',
            'rera_number' => $validated['rera_number'],
            'project_type' => $validated['project_type'],
            'visibility' => $validated['visibility'] ?? 'public',
            'banner_image' => $bannerPath,
            'amenities' => ['Clubhouse', 'Swimming Pool', 'Gym', 'EV Parking'],
            'status' => 'active',
        ]);

        // Auto create Tower 1 and 5 units
        $building = ProjectBuilding::create([
            'company_id' => Auth::user()->company_id ?? 1,
            'project_id' => $project->id,
            'name' => 'Tower 1',
            'code' => 'T1',
            'total_floors' => 5,
            'total_units' => 5,
        ]);

        $floor = ProjectFloor::create([
            'company_id' => Auth::user()->company_id ?? 1,
            'building_id' => $building->id,
            'floor_number' => 1,
            'name' => 'Floor 1',
            'total_units' => 5,
        ]);

        for ($i = 101; $i <= 105; $i++) {
            Unit::create([
                'company_id' => Auth::user()->company_id ?? 1,
                'project_id' => $project->id,
                'building_id' => $building->id,
                'floor_id' => $floor->id,
                'unit_number' => (string)$i,
                'unit_type' => ($i % 2 == 0) ? '3BHK' : '2BHK',
                'carpet_area' => 1200,
                'builtup_area' => 1450,
                'super_builtup_area' => 1650,
                'base_price' => 7000000,
                'final_price' => 7800000,
                'status' => 'available',
            ]);
        }

        return redirect()->route('projects.index')->with('success', "Project {$project->name} & Banner Image uploaded successfully!");
    }

    public function update(Request $request, Project $project, StorageService $storageService)
    {
        Gate::authorize('manage-projects');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'city' => 'nullable|string|max:100',
            'rera_number' => 'nullable|string|max:100',
            'project_type' => 'required|string',
            'visibility' => 'nullable|in:public,private',
            'banner_image' => 'nullable|file|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        if ($request->hasFile('banner_image')) {
            $project->banner_image = $storageService->uploadToPublic($request->file('banner_image'), 'projects');
        }

        $project->name = $validated['name'];
        $project->code = $validated['code'];
        $project->city = $validated['city'] ?? $project->city;
        $project->rera_number = $validated['rera_number'] ?? $project->rera_number;
        $project->project_type = $validated['project_type'];
        $project->visibility = $validated['visibility'] ?? 'public';
        $project->save();

        return redirect()->route('projects.index')->with('success', "Project {$project->name} & Banner Image updated successfully!");
    }

    public function destroy(Project $project)
    {
        Gate::authorize('manage-projects');

        $name = $project->name;
        $project->delete();

        return redirect()->route('projects.index')->with('success', "Project {$name} deleted successfully!");
    }

    public function updateUnitStatus(Request $request, Unit $unit)
    {
        Gate::authorize('manage-inventory');

        $request->validate(['status' => 'required|string']);

        $unit->update([
            'status' => $request->status,
            'hold_by_user_id' => ($request->status === 'hold') ? Auth::id() : null,
            'holding_expires_at' => ($request->status === 'hold') ? now()->addDays(2) : null,
        ]);

        return back()->with('success', "Unit {$unit->unit_number} status updated to {$request->status}.");
    }

    public function destroyUnit(Unit $unit)
    {
        Gate::authorize('manage-projects');

        $unitNumber = $unit->unit_number;
        $unit->delete();

        return back()->with('success', "Inventory Unit {$unitNumber} deleted successfully.");
    }

    public function storeBuilding(Request $request, Project $project)
    {
        Gate::authorize('manage-projects');

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20',
            'total_floors' => 'required|integer|min:1',
            'total_units' => 'nullable|integer',
        ]);

        $building = ProjectBuilding::create([
            'company_id' => $project->company_id,
            'project_id' => $project->id,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'total_floors' => $validated['total_floors'],
            'total_units' => $validated['total_units'] ?? 0,
        ]);

        // Auto create floors
        for ($f = 1; $f <= $validated['total_floors']; $f++) {
            ProjectFloor::firstOrCreate([
                'building_id' => $building->id,
                'floor_number' => $f,
            ], [
                'company_id' => $project->company_id,
                'name' => "Floor {$f}",
                'total_units' => 0,
            ]);
        }

        return back()->with('success', "Building/Tower '{$building->name}' added to Project {$project->name}!");
    }

    public function storeUnit(Request $request, Project $project)
    {
        Gate::authorize('manage-projects');

        $validated = $request->validate([
            'building_id' => 'required|exists:project_buildings,id',
            'unit_number' => 'required|string|max:20',
            'unit_type' => 'required|string',
            'floor_number' => 'nullable|integer',
            'carpet_area' => 'required|numeric',
            'base_price' => 'required|numeric',
            'final_price' => 'required|numeric',
        ]);

        $building = ProjectBuilding::findOrFail($validated['building_id']);

        $floor = ProjectFloor::firstOrCreate([
            'building_id' => $building->id,
            'floor_number' => $validated['floor_number'] ?? 1,
        ], [
            'company_id' => $project->company_id,
            'name' => "Floor " . ($validated['floor_number'] ?? 1),
            'total_units' => 0,
        ]);

        $unit = Unit::create([
            'company_id' => $project->company_id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'unit_number' => $validated['unit_number'],
            'unit_type' => $validated['unit_type'],
            'carpet_area' => $validated['carpet_area'],
            'builtup_area' => $validated['carpet_area'] * 1.2,
            'super_builtup_area' => $validated['carpet_area'] * 1.35,
            'base_price' => $validated['base_price'],
            'final_price' => $validated['final_price'],
            'status' => 'available',
        ]);

        return back()->with('success', "Unit {$unit->unit_number} ({$unit->unit_type}) added to {$building->name}!");
    }

    public function show($id)
    {
        if (Auth::user()->isBroker()) {
            return redirect()->route('dashboard');
        }

        $user = Auth::user();
        $query = Project::with(['buildings.floors', 'buildings.units', 'units']);

        if ($user->isSaaSFounder()) {
            $project = Project::withoutGlobalScopes()->with(['company', 'buildings.floors', 'buildings.units' => function ($uq) {
                $uq->withoutGlobalScopes();
            }, 'units' => function ($uq) {
                $uq->withoutGlobalScopes();
            }])->findOrFail($id);

            $recentBookings = \App\Models\Booking::withoutGlobalScopes()
                ->where('project_id', $project->id)
                ->with(['lead', 'unit' => fn($q) => $q->withoutGlobalScopes(), 'salesUser', 'broker'])
                ->latest()
                ->take(15)
                ->get();

            $projectLeads = \App\Models\Lead::withoutGlobalScopes()
                ->where('interested_project_id', $project->id)
                ->with('assignedTo')
                ->latest()
                ->take(15)
                ->get();
        } else {
            $project = $query->findOrFail($id);

            $recentBookings = \App\Models\Booking::where('project_id', $project->id)
                ->with(['lead', 'unit', 'salesUser', 'broker'])
                ->latest()
                ->take(15)
                ->get();

            $projectLeads = \App\Models\Lead::where('interested_project_id', $project->id)
                ->with('assignedTo')
                ->latest()
                ->take(15)
                ->get();
        }

        return view('projects.show', compact('project', 'recentBookings', 'projectLeads'));
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        Gate::authorize('manage-projects');

        $validated = $request->validate([
            'unit_number' => 'required|string|max:20',
            'unit_type' => 'required|string',
            'floor_number' => 'nullable|integer',
            'carpet_area' => 'required|numeric',
            'base_price' => 'required|numeric',
            'final_price' => 'required|numeric',
        ]);

        $oldBasePrice = $unit->base_price;
        $oldTotalPrice = $unit->final_price ?? $unit->total_price ?? 0;

        $unit->update([
            'unit_number' => $validated['unit_number'],
            'unit_type' => $validated['unit_type'],
            'carpet_area' => $validated['carpet_area'],
            'builtup_area' => $validated['carpet_area'] * 1.2,
            'super_builtup_area' => $validated['carpet_area'] * 1.35,
            'base_price' => $validated['base_price'],
            'final_price' => $validated['final_price'],
        ]);

        if ($oldBasePrice != $validated['base_price'] || $oldTotalPrice != $validated['final_price']) {
            \App\Models\UnitPriceHistory::create([
                'company_id' => $unit->company_id,
                'unit_id' => $unit->id,
                'updated_by_user_id' => Auth::id(),
                'old_base_price' => $oldBasePrice,
                'new_base_price' => $validated['base_price'],
                'old_total_price' => $oldTotalPrice,
                'new_total_price' => $validated['final_price'],
                'change_reason' => $request->input('change_reason', 'Price revision by management'),
            ]);
        }

        return back()->with('success', "Unit {$unit->unit_number} updated & price revision logged successfully!");
    }

    public function publicShow($id, Request $request)
    {
        $project = Project::withoutGlobalScopes()
            ->with(['company', 'buildings.floors', 'units' => function ($q) {
                $q->withoutGlobalScopes();
            }])
            ->findOrFail($id);

        $broker = null;
        if ($request->has('ref')) {
            $broker = \App\Models\Broker::withoutGlobalScopes()
                ->where('id', $request->query('ref'))
                ->orWhere('code', $request->query('ref'))
                ->first();
        }

        $availableUnitsCount = $project->units->where('status', 'available')->count();
        $unitTypes = $project->units->pluck('unit_type')->unique()->filter()->values();

        $minPrice = $project->units->where('status', 'available')->min('final_price')
            ?? $project->units->min('final_price')
            ?? $project->units->min('base_price');

        $minCarpetArea = $project->units->min('carpet_area');
        $maxCarpetArea = $project->units->max('carpet_area');

        return view('projects.public', compact('project', 'broker', 'availableUnitsCount', 'unitTypes', 'minPrice', 'minCarpetArea', 'maxCarpetArea'));
    }

    public function storePublicInquiry(Request $request, $id)
    {
        $project = Project::withoutGlobalScopes()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'interested_unit_type' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'broker_id' => 'nullable|exists:brokers,id',
        ]);

        $nameParts = explode(' ', trim($validated['name']), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $lastLeadId = \App\Models\Lead::withoutGlobalScopes()->max('id') + 1;
        $leadCode = 'LEAD-PUB-' . str_pad($lastLeadId, 4, '0', STR_PAD_LEFT);

        $source = \App\Models\LeadSource::withoutGlobalScopes()
            ->where('company_id', $project->company_id)
            ->where('name', 'Public Share Link')
            ->first();

        if (!$source) {
            $source = \App\Models\LeadSource::create([
                'company_id' => $project->company_id,
                'name' => 'Public Share Link',
                'slug' => 'public-share-link',
                'is_active' => true,
            ]);
        }

        $brokerId = $validated['broker_id'] ?? null;
        if (!$brokerId && $request->has('ref')) {
            $broker = \App\Models\Broker::withoutGlobalScopes()
                ->where('id', $request->query('ref'))
                ->orWhere('code', $request->query('ref'))
                ->first();
            if ($broker) {
                $brokerId = $broker->id;
            }
        }

        \App\Models\Lead::create([
            'company_id' => $project->company_id,
            'lead_code' => $leadCode,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'interested_project_id' => $project->id,
            'interested_unit_type' => $validated['interested_unit_type'] ?? null,
            'source_id' => $source->id,
            'broker_id' => $brokerId,
            'status' => 'new',
            'notes' => 'Submitted via Public Project Showcase Link. ' . ($validated['notes'] ?? ''),
        ]);

        return back()->with('inquiry_success', 'Thank you! Your inquiry has been received. Our sales team will get in touch with you shortly.');
    }
}
