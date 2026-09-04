<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanySubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->company) {
            $company = $user->company;

            // Allow dashboard viewing, auth, subscription selection, and admin controls even if pending subscription
            if ($request->routeIs('dashboard', 'subscription.select-plan', 'logout', 'admin.*') || 
                $request->is('api/subscription/*') || 
                $request->is('api/me') || 
                $request->is('api/auth/*')) {
                return $next($request);
            }

            if (!$company->isSubscriptionActive()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'error_code' => 'SUBSCRIPTION_EXPIRED',
                        'message' => 'Your company subscription has expired or is suspended. Please select a subscription plan to continue.',
                        'subscription_summary' => $company->usageSummary(),
                    ], 402);
                }

                return redirect()->route('dashboard')->with('warning', 'Please select a SaaS subscription plan to activate your workspace.');
            }
        }

        return $next($request);
    }
}
