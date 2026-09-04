<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanySettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isSaaSFounder()) {
            return redirect()->route('admin.companies.index')->with('info', 'SaaS Founder Scope: Manage tenant builder companies, plans, and subscriptions here.');
        }

        $company = $user->company;

        return view('company_settings.index', compact('user', 'company'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return back()->with('error', 'No company attached to logged-in admin.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'rera_number' => 'nullable|string|max:100',
            'gstin' => 'nullable|string|max:50',
        ]);

        $company->update($validated);

        return back()->with('success', 'Company details and settings updated successfully!');
    }
}
