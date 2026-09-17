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
        if ($user->isDirectorOrFounder()) {
            $myPermissions = \App\Models\Permission::pluck('slug')->toArray();
        } elseif ($user->role) {
            $myPermissions = $user->role->permissions()->pluck('slug')->toArray();
        }

        $allPermissionsGrouped = \App\Models\Permission::all()->groupBy('module');

        return view('profile.permissions', [
            'user' => $user,
            'myPermissions' => $myPermissions,
            'allPermissionsGrouped' => $allPermissionsGrouped,
        ]);
    }
}
