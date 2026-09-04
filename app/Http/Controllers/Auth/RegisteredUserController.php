<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the company onboarding registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming company onboarding registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'company_code' => ['required', 'string', 'max:20'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'subscription_plan_id' => ['nullable'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            // 1. Create Tenant Company (Pending Subscription until chosen on Dashboard)
            $company = Company::create([
                'name' => $request->company_name,
                'code' => strtoupper($request->company_code),
                'slug' => Str::slug($request->company_name) . '-' . rand(100, 999),
                'email' => $request->email,
                'phone' => $request->phone,
                'tax_number' => $request->tax_number,
                'status' => 'pending_subscription',
                'subscription_plan_id' => null,
                'subscription_expires_at' => null,
                'settings' => ['onboarding_source' => 'self_registered'],
            ]);

            // 2. Create Standard Company Roles
            $roleSlugs = [
                'founder' => 'Founder / Director',
                'director' => 'Director',
                'admin' => 'Company Admin',
                'manager' => 'Sales Manager',
                'sales_executive' => 'Sales Executive',
                'field_team' => 'Field Executive',
                'support_team' => 'Support Desk',
                'broker' => 'Channel Partner / Broker',
            ];

            $adminRole = null;
            foreach ($roleSlugs as $slug => $roleName) {
                $role = Role::create([
                    'company_id' => $company->id,
                    'name' => $roleName,
                    'slug' => $slug,
                    'description' => "{$roleName} role for {$company->name}",
                ]);

                if ($slug === 'admin') {
                    $adminRole = $role;
                }
            }

            // 3. Create Admin User
            return User::create([
                'company_id' => $company->id,
                'role_id' => $adminRole?->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))->with('success', "Welcome! Your company {$user->company->name} has been successfully registered on REOS!");
    }
}
