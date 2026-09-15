<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileAppRole
{
    /**
     * Mobile app is currently available only for these operational roles.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $isBrokerAccount = $user && ($user->role?->slug === 'broker'
            || \App\Models\Broker::where('user_id', $user->id)->exists());

        if (!$user || (!in_array($user->role?->slug, ['manager', 'sales_executive', 'executive', 'broker'], true) && !$isBrokerAccount)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This mobile app is available only for managers, executives, and brokers.',
            ], 403);
        }

        return $next($request);
    }
}
