<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManagerTeamApiController extends Controller
{
    protected function teamQuery(User $manager)
    {
        return User::where('company_id', $manager->company_id)
            ->where('reporting_manager_id', $manager->id)
            ->whereHas('role', fn ($query) => $query->whereIn('slug', ['sales_executive', 'executive']))
            ->with('role')
            ->withCount([
                'assignedLeads as total_leads',
                'assignedLeads as converted_leads' => fn ($query) => $query->whereIn('status', ['converted', 'booked']),
            ])
            ->latest();
    }

    public function index(Request $request)
    {
        $team = $this->teamQuery($request->user())
            ->paginate(min((int) $request->get('per_page', 20), 100));

        return response()->json([
            'status' => 'success',
            'data' => $team,
        ]);
    }

    public function store(Request $request)
    {
        $manager = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'branch' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'role' => ['nullable', Rule::in(['sales_executive', 'executive'])],
            'password' => 'required|string|min:6|confirmed',
        ]);

        $role = Role::where(function ($query) use ($manager) {
            $query->whereNull('company_id')->orWhere('company_id', $manager->company_id);
        })->where('slug', $validated['role'] ?? 'sales_executive')->first();

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Executive role is not configured for this company.',
            ], 422);
        }

        $executive = User::create([
            'company_id' => $manager->company_id,
            'role_id' => $role->id,
            'reporting_manager_id' => $manager->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'branch' => $validated['branch'] ?? $manager->branch,
            'department' => $validated['department'] ?? 'Sales',
            'designation' => $validated['designation'] ?? 'Executive',
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Executive added to your team successfully.',
            'data' => $executive->load('role'),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $manager = $request->user();
        $executive = $this->teamQuery($manager)->whereKey($id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($executive->id)],
            'phone' => 'sometimes|required|string|max:20',
            'branch' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
            'is_active' => 'sometimes|boolean',
        ]);

        $update = collect($validated)->except('password')->all();
        if (!empty($validated['password'])) {
            $update['password'] = Hash::make($validated['password']);
        }
        $executive->update($update);

        return response()->json([
            'status' => 'success',
            'message' => 'Team executive updated successfully.',
            'data' => $executive->fresh('role'),
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate(['is_active' => 'required|boolean']);
        $executive = $this->teamQuery($request->user())->whereKey($id)->firstOrFail();
        $executive->update(['is_active' => $validated['is_active']]);

        return response()->json([
            'status' => 'success',
            'message' => $validated['is_active'] ? 'Executive activated.' : 'Executive deactivated.',
            'data' => $executive->fresh('role'),
        ]);
    }
}
