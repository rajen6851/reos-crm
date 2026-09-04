<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage-users');

        $user = Auth::user();
        
        $query = User::where('company_id', $user->company_id)
            ->where('is_super_admin', false)
            ->whereHas('role', function ($q) {
                $q->where('slug', '!=', 'broker');
            });

        // Sales Managers can only manage Sales Executives
        if ($user->isManager()) {
            $query->whereHas('role', function ($q) {
                $q->where('slug', 'sales_executive');
            });
        }

        $users = $query->with('role')
            ->withCount([
                'leads as total_leads',
                'leads as converted_leads' => function ($q) {
                    $q->where('status', 'converted');
                }
            ])
            ->latest()
            ->get();

        $rolesQuery = Role::where(function ($q) use ($user) {
            $q->whereNull('company_id')->orWhere('company_id', $user->company_id);
        })->where('slug', '!=', 'broker');

        if ($user->isManager()) {
            $rolesQuery->where('slug', 'sales_executive');
        }

        $roles = $rolesQuery->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-users');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'branch' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:6',
            'agency_name' => 'nullable|string|max:150',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $currentUser = Auth::user();
        $role = Role::findOrFail($validated['role_id']);

        if ($currentUser->isManager() && $role->slug !== 'sales_executive') {
            return back()->with('error', 'Sales Managers can only create Sales Executive accounts.');
        }

        $newUser = User::create([
            'company_id' => $currentUser->company_id,
            'role_id' => $role->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'branch' => $validated['branch'] ?? 'Head Office',
            'department' => $validated['department'] ?? 'Sales',
            'designation' => $validated['designation'] ?? 'Executive',
            'password' => Hash::make($validated['password']),
        ]);

        // If the role is broker, automatically create Broker record
        if ($role->slug === 'broker') {
            Broker::create([
                'company_id' => $currentUser->company_id,
                'user_id' => $newUser->id,
                'agency_name' => $validated['agency_name'] ?? ($newUser->name . ' Realty'),
                'broker_code' => 'BRK-' . rand(1000, 9999),
                'phone' => $newUser->phone,
                'email' => $newUser->email,
                'commission_rate' => $validated['commission_rate'] ?? 2.50,
                'status' => 'active',
            ]);
        }

        return redirect()->route('users.index')->with('success', "Team user {$newUser->name} created successfully!");
    }

    public function update(Request $request, User $user)
    {
        Gate::authorize('manage-users');

        $currentUser = Auth::user();
        if ($currentUser->isManager() && $user->role?->slug !== 'sales_executive') {
            return back()->with('error', 'Sales Managers can only edit Sales Executive accounts.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'branch' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:6',
        ]);

        $targetRole = Role::findOrFail($validated['role_id']);
        if ($currentUser->isManager() && $targetRole->slug !== 'sales_executive') {
            return back()->with('error', 'Sales Managers can only assign the Sales Executive role.');
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'branch' => $validated['branch'],
            'department' => $validated['department'],
            'designation' => $validated['designation'],
            'role_id' => $validated['role_id'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')->with('success', "Sales Executive {$user->name} updated successfully!");
    }

    public function destroy(User $user)
    {
        Gate::authorize('manage-users');

        $currentUser = Auth::user();
        if ($currentUser->isManager() && $user->role?->slug !== 'sales_executive') {
            return back()->with('error', 'Sales Managers can only delete Sales Executive accounts.');
        }

        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot delete your own logged-in admin account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "Sales Executive {$name} deleted successfully!");
    }
}
