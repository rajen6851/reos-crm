<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributionRule;
use App\Models\DistributionRuleMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistributionRuleApiController extends Controller
{
    /**
     * List all distribution rules for the company.
     */
    public function index(Request $request)
    {
        $rules = DistributionRule::where('company_id', $request->user()->company_id)
            ->with('members.user:id,first_name,last_name,email')
            ->orderBy('priority', 'asc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $rules]);
    }

    /**
     * Create a new distribution rule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'priority' => 'integer|min:1',
            'distribution_method' => 'required|in:round_robin,percentage,fixed_quantity',
            'fallback_behavior' => 'in:redistribute,skip',
            'is_active' => 'boolean',
            'members' => 'required|array|min:1',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.allocation_value' => 'nullable|numeric|min:0',
        ]);

        // Percentage validation
        if ($validated['distribution_method'] === 'percentage') {
            $totalPercentage = collect($validated['members'])->sum('allocation_value');
            if ($totalPercentage != 100) {
                return response()->json(['error' => 'Total allocation value must be exactly 100 for percentage distribution.'], 422);
            }
        }

        DB::beginTransaction();
        try {
            $rule = DistributionRule::create([
                'company_id' => $request->user()->company_id,
                'name' => $validated['name'],
                'project_id' => $validated['project_id'] ?? null,
                'lead_source_id' => $validated['lead_source_id'] ?? null,
                'priority' => $validated['priority'] ?? 5,
                'distribution_method' => $validated['distribution_method'],
                'fallback_behavior' => $validated['fallback_behavior'] ?? 'redistribute',
                'is_active' => $validated['is_active'] ?? true,
                'version' => 1,
            ]);

            foreach ($validated['members'] as $member) {
                DistributionRuleMember::create([
                    'distribution_rule_id' => $rule->id,
                    'user_id' => $member['user_id'],
                    'allocation_value' => $member['allocation_value'] ?? null,
                ]);
            }

            DB::commit();
            
            $rule->load('members');
            return response()->json(['status' => 'success', 'message' => 'Distribution Rule created successfully.', 'data' => $rule], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create rule.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a specific rule.
     */
    public function show(Request $request, $id)
    {
        $rule = DistributionRule::where('company_id', $request->user()->company_id)
            ->with('members.user:id,first_name,last_name')
            ->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $rule]);
    }

    /**
     * Update a distribution rule.
     */
    public function update(Request $request, $id)
    {
        $rule = DistributionRule::where('company_id', $request->user()->company_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'priority' => 'sometimes|integer|min:1',
            'distribution_method' => 'sometimes|in:round_robin,percentage,fixed_quantity',
            'fallback_behavior' => 'sometimes|in:redistribute,skip',
            'is_active' => 'sometimes|boolean',
            'members' => 'sometimes|array|min:1',
            'members.*.user_id' => 'required_with:members|exists:users,id',
            'members.*.allocation_value' => 'nullable|numeric|min:0',
        ]);

        if (isset($validated['members']) && ($validated['distribution_method'] ?? $rule->distribution_method) === 'percentage') {
            $totalPercentage = collect($validated['members'])->sum('allocation_value');
            if ($totalPercentage != 100) {
                return response()->json(['error' => 'Total allocation value must be exactly 100 for percentage distribution.'], 422);
            }
        }

        DB::beginTransaction();
        try {
            $rule->update([
                'name' => $validated['name'] ?? $rule->name,
                'project_id' => array_key_exists('project_id', $validated) ? $validated['project_id'] : $rule->project_id,
                'lead_source_id' => array_key_exists('lead_source_id', $validated) ? $validated['lead_source_id'] : $rule->lead_source_id,
                'priority' => $validated['priority'] ?? $rule->priority,
                'distribution_method' => $validated['distribution_method'] ?? $rule->distribution_method,
                'fallback_behavior' => $validated['fallback_behavior'] ?? $rule->fallback_behavior,
                'is_active' => $validated['is_active'] ?? $rule->is_active,
                'version' => $rule->version + 1, // Increment version on update
            ]);

            if (isset($validated['members'])) {
                // Remove old members and add new ones (simpler than syncing with custom values)
                $rule->members()->delete();
                foreach ($validated['members'] as $member) {
                    DistributionRuleMember::create([
                        'distribution_rule_id' => $rule->id,
                        'user_id' => $member['user_id'],
                        'allocation_value' => $member['allocation_value'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $rule->load('members');
            return response()->json(['status' => 'success', 'message' => 'Distribution Rule updated successfully.', 'data' => $rule]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update rule.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a rule.
     */
    public function destroy(Request $request, $id)
    {
        $rule = DistributionRule::where('company_id', $request->user()->company_id)->findOrFail($id);
        
        $rule->members()->delete();
        $rule->states()->delete();
        $rule->memberStates()->delete();
        $rule->delete();

        return response()->json(['status' => 'success', 'message' => 'Distribution Rule deleted successfully.']);
    }
}
