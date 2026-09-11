<?php

namespace App\Http\Controllers;

use App\Models\LeadSource;
use App\Models\Project;
use App\Services\LeadSources\LeadSourceManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LeadSourceController extends Controller
{
    protected function ensureSchemaMigrated(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('lead_sources', 'type')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Auto-migrate lead_sources failed: " . $e->getMessage());
            }
        }
    }

    public function index(LeadSourceManager $manager)
    {
        $this->ensureSchemaMigrated();

        $user = Auth::user();
        if (!$user->company_id && !$user->isSaaSFounder()) {
            return redirect()->route('dashboard')->with('error', 'No company associated with your account.');
        }

        $leadSources = LeadSource::where('company_id', $user->company_id)
            ->orderBy('id', 'desc')
            ->get();

        $supportedTypes = $manager->getSupportedTypes();
        $projects = Project::where('company_id', $user->company_id)->get();

        return view('lead_sources.index', compact('leadSources', 'supportedTypes', 'projects', 'user'));
    }

    public function store(Request $request, LeadSourceManager $manager)
    {
        $this->ensureSchemaMigrated();

        $user = Auth::user();


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:meta,google,99acres,magicbricks,housing,website,custom_api',
            'default_project_id' => 'nullable|exists:projects,id',
            'credentials' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $leadSource = LeadSource::create([
            'company_id' => $user->company_id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name'] . '-' . Str::random(4)),
            'type' => $validated['type'],
            'webhook_token' => Str::random(32),
            'status' => 'connected',
            'credentials' => $validated['credentials'] ?? [],
            'settings' => [
                'default_project_id' => $validated['default_project_id'] ?? null,
            ],
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return redirect()->route('lead-sources.index')->with('success', "Lead Source '{$leadSource->name}' connected successfully!");
    }

    public function update(Request $request, LeadSource $leadSource)
    {
        $user = Auth::user();
        if ($leadSource->company_id !== $user->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_project_id' => 'nullable|exists:projects,id',
            'credentials' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $existingSettings = $leadSource->settings ?? [];
        $existingSettings['default_project_id'] = $validated['default_project_id'] ?? null;

        $leadSource->update([
            'name' => $validated['name'],
            'credentials' => array_merge($leadSource->credentials ?? [], $validated['credentials'] ?? []),
            'settings' => $existingSettings,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : false,
        ]);

        return redirect()->route('lead-sources.index')->with('success', "Lead Source '{$leadSource->name}' updated successfully!");
    }

    public function testConnection(LeadSource $leadSource, LeadSourceManager $manager)
    {
        $user = Auth::user();
        if ($leadSource->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $adapter = $manager->getAdapter($leadSource->type);
        $result = $adapter->testConnection($leadSource);

        if ($result['success']) {
            $leadSource->update([
                'status' => 'connected',
                'error_log' => null,
            ]);
        } else {
            $leadSource->update([
                'status' => 'error',
                'error_log' => $result['message'],
            ]);
        }

        return response()->json($result);
    }

    public function destroy(LeadSource $leadSource)
    {
        $user = Auth::user();
        if ($leadSource->company_id !== $user->company_id) {
            abort(403);
        }

        $leadSource->delete();

        return redirect()->route('lead-sources.index')->with('success', 'Lead Source deleted successfully.');
    }
}
