<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\DuplicateLeadService;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isBroker()) {
            return response()->json(['error' => 'Brokers must use broker portal API endpoints.'], 403);
        }

        $query = Lead::with(['assignedTo', 'broker', 'project', 'source']);

        if ($user->isSales()) {
            $query->where('assigned_to_user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leads = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($leads);
    }

    public function store(Request $request, DuplicateLeadService $duplicateService)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:20',
            'source_id' => 'nullable|exists:lead_sources,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'interested_project_id' => 'nullable|exists:projects,id',
            'interested_unit_type' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $duplicate = $duplicateService->findDuplicate($user->company_id, $validated['phone'], $validated['email'] ?? null);

        $lead = Lead::create(array_merge($validated, [
            'company_id' => $user->company_id,
            'lead_code' => 'LD-' . rand(1000, 9999),
            'status' => 'new',
            'is_duplicate' => $duplicate ? true : false,
            'duplicate_of_lead_id' => $duplicate ? $duplicate->id : null,
        ]));

        if ($user->isSales() && !$lead->assigned_to_user_id) {
            $lead->update(['assigned_to_user_id' => $user->id]);
        }

        return response()->json([
            'status' => 'success',
            'message' => $duplicate ? 'Lead created with duplicate flag.' : 'Lead created successfully.',
            'is_duplicate' => (bool)$duplicate,
            'data' => $lead,
        ], 201);
    }

    public function updateStatus(Request $request, Lead $lead, LeadService $leadService)
    {
        $user = $request->user();

        if ($lead->company_id !== $user->company_id) {
            return response()->json(['error' => 'Unauthorized access to lead.'], 403);
        }

        $request->validate(['status' => 'required|string']);

        try {
            $leadService->updateStatus($lead, $request->status, $request->get('notes'), $user);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lead status updated and broker view synchronized.',
            'lead' => $lead->fresh(['project', 'broker', 'brokerLead']),
        ]);
    }
}
