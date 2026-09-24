<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSaaSAdmin() || !$user->hasSaaSPermission('manage_subscriptions')) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'max_users' => 'required|integer|min:1',
            'max_projects' => 'required|integer|min:1',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        SubscriptionPlan::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . uniqid(),
            'price' => $validated['price'],
            'max_users' => $validated['max_users'],
            'max_projects' => $validated['max_projects'],
            'billing_cycle' => $validated['billing_cycle'],
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'SaaS Subscription Plan created successfully!');
    }
}
