<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the user's permissions screen.
     */
    public function myPermissions(Request $request): View
    {
        $user = $request->user();
        
        $myPermissions = [];
        if ($user->isSaaSAdmin()) {
            $availableSaasPermissions = [
                'view_companies' => ['name' => 'View Builder Companies', 'description' => 'Access and inspect registered builder tenant companies', 'module' => 'Platform', 'icon' => 'fa-building'],
                'onboard_companies' => ['name' => 'Onboard New Companies', 'description' => 'Create and onboard new tenant builder accounts', 'module' => 'Platform', 'icon' => 'fa-plus-circle'],
                'edit_companies' => ['name' => 'Edit Company Details', 'description' => 'Modify builder company settings and credentials', 'module' => 'Platform', 'icon' => 'fa-edit'],
                'delete_companies' => ['name' => 'Delete Builder Company', 'description' => 'Delete builder tenant company (Sub-Admins require SaaS Founder approval)', 'module' => 'Platform', 'icon' => 'fa-trash-alt'],
                'manage_subscriptions' => ['name' => 'Subscription Management', 'description' => 'Modify company SaaS plans and subscription statuses', 'module' => 'Subscriptions', 'icon' => 'fa-bolt'],
                'delete_plans' => ['name' => 'Delete SaaS Plans', 'description' => 'Delete SaaS pricing plans (Sub-Admins require SaaS Founder approval)', 'module' => 'Subscriptions', 'icon' => 'fa-trash-can'],
                'manage_subadmins' => ['name' => 'Manage SaaS Sub-Admins', 'description' => 'Create and configure other SaaS Sub-Admin accounts & permissions', 'module' => 'Security', 'icon' => 'fa-user-shield'],
            ];

            if ($user->is_super_admin) {
                $myPermissions = array_keys($availableSaasPermissions);
            } else {
                $myPermissions = $user->saas_permissions ?? [];
            }

            $allPermissionsGrouped = collect($availableSaasPermissions)->map(function($item, $key) {
                return (object) [
                    'slug' => $key,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'module' => $item['module'],
                    'icon' => $item['icon'],
                ];
            })->groupBy('module');

        } else {
            if ($user->isDirectorOrFounder()) {
                $myPermissions = \App\Models\Permission::pluck('slug')->toArray();
            } elseif ($user->role) {
                $myPermissions = $user->role->permissions()->pluck('slug')->toArray();
            }
            $allPermissionsGrouped = \App\Models\Permission::all()->groupBy('module');
        }

        return view('profile.permissions', [
            'user' => $user,
            'myPermissions' => $myPermissions,
            'allPermissionsGrouped' => $allPermissionsGrouped,
        ]);
    }
}
