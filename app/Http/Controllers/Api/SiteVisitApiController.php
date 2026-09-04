<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\SiteVisit;
use App\Services\SiteVisitService;
use Illuminate\Http\Request;

class SiteVisitApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = SiteVisit::where('company_id', $user->company_id)
            ->with(['lead', 'project', 'assignedTo']);

        if ($user->isSales()) {
            $query->where('assigned_to_user_id', $user->id);
        }

        $siteVisits = $query->latest('scheduled_at')->paginate($request->get('per_page', 15));
        return response()->json($siteVisits);
    }

    public function store(Request $request, SiteVisitService $visitService)
    {
        $user = $request->user();

        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'scheduled_at' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'pickup_location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Tenant scope check for lead
        Lead::where('company_id', $user->company_id)->findOrFail($validated['lead_id']);

        $siteVisit = $visitService->scheduleVisit($validated, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Site visit scheduled successfully.',
            'data' => $siteVisit->fresh(['lead', 'project']),
        ], 201);
    }
}
