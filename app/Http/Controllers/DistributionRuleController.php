<?php

namespace App\Http\Controllers;

use App\Models\DistributionRule;
use App\Models\DistributionRuleMember;
use App\Models\Project;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistributionRuleController extends Controller
{
    public function index(Request $request)
    {
        $rules = DistributionRule::where('company_id', $request->user()->company_id)
            ->with(['members.user', 'project', 'source'])
            ->orderBy('priority', 'asc')
            ->get();

        return view('settings.distribution_rules.index', compact('rules'));
    }

    public function create(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $projects = Project::where('company_id', $companyId)->get();
        $sources = LeadSource::where('company_id', $companyId)->get();
        // Get active sales executives and managers
        $users = User::where('company_id', $companyId)
            ->whereIn('role_id', [2, 3, 4]) // Assuming 2=Manager, 3=Sales
            ->where('is_active', true)
            ->get();

        return view('settings.distribution_rules.create', compact('projects', 'sources', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'priority' => 'required|integer|min:1',
            'distribution_method' => 'required|in:round_robin,percentage,fixed_quantity,performance',
            'fallback_behavior' => 'required|in:redistribute,skip',
            'is_active' => 'nullable|boolean',
            'members' => 'required|array|min:1',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.allocation_value' => 'nullable|numeric|min:0',
        ]);

        if ($validated['distribution_method'] === 'percentage') {
            $totalPercentage = collect($validated['members'])->sum('allocation_value');
            if ($totalPercentage != 100) {
                return back()->withInput()->withErrors(['percentage_error' => 'Total allocation value must be exactly 100 for percentage distribution.']);
            }
        }

        DB::beginTransaction();
        try {
            $rule = DistributionRule::create([
                'company_id' => $request->user()->company_id,
                'name' => $validated['name'],
                'project_id' => $validated['project_id'] ?? null,
                'lead_source_id' => $validated['lead_source_id'] ?? null,
                'priority' => $validated['priority'],
                'distribution_method' => $validated['distribution_method'],
                'fallback_behavior' => $validated['fallback_behavior'],
                'is_active' => $request->has('is_active'),
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
            return redirect()->route('distribution-rules.index')->with('success', 'Distribution Rule created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create rule: ' . $e->getMessage());
        }
    }

    public function edit(Request $request, $id)
    {
        $rule = DistributionRule::where('company_id', $request->user()->company_id)
            ->with('members')
            ->findOrFail($id);

        $companyId = $request->user()->company_id;
        $projects = Project::where('company_id', $companyId)->get();
        $sources = LeadSource::where('company_id', $companyId)->get();
        $users = User::where('company_id', $companyId)
            ->whereIn('role_id', [2, 3, 4])
            ->where('is_active', true)
            ->get();

        return view('settings.distribution_rules.edit', compact('rule', 'projects', 'sources', 'users'));
    }

    public function update(Request $request, $id)
    {
        $rule = DistributionRule::where('company_id', $request->user()->company_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'priority' => 'required|integer|min:1',
            'distribution_method' => 'required|in:round_robin,percentage,fixed_quantity,performance',
            'fallback_behavior' => 'required|in:redistribute,skip',
            'is_active' => 'nullable|boolean',
            'members' => 'required|array|min:1',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.allocation_value' => 'nullable|numeric|min:0',
        ]);

        if ($validated['distribution_method'] === 'percentage') {
            $totalPercentage = collect($validated['members'])->sum('allocation_value');
            if ($totalPercentage != 100) {
                return back()->withInput()->withErrors(['percentage_error' => 'Total allocation value must be exactly 100 for percentage distribution.']);
            }
        }

        DB::beginTransaction();
        try {
            $rule->update([
                'name' => $validated['name'],
                'project_id' => $validated['project_id'] ?? null,
                'lead_source_id' => $validated['lead_source_id'] ?? null,
                'priority' => $validated['priority'],
                'distribution_method' => $validated['distribution_method'],
                'fallback_behavior' => $validated['fallback_behavior'],
                'is_active' => $request->has('is_active'),
                'version' => $rule->version + 1,
            ]);

            $rule->members()->delete();
            foreach ($validated['members'] as $member) {
                DistributionRuleMember::create([
                    'distribution_rule_id' => $rule->id,
                    'user_id' => $member['user_id'],
                    'allocation_value' => $member['allocation_value'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('distribution-rules.index')->with('success', 'Distribution Rule updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update rule: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        $rule = DistributionRule::where('company_id', $request->user()->company_id)->findOrFail($id);
        
        $rule->members()->delete();
        $rule->states()->delete();
        $rule->memberStates()->delete();
        $rule->delete();

        return redirect()->route('distribution-rules.index')->with('success', 'Distribution Rule deleted successfully.');
    }
}
