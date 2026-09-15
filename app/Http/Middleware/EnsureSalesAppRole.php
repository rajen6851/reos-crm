<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSalesAppRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isSales() && !$request->user()?->isManager()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sales executive or manager role is required for these API endpoints.',
            ], 403);
        }

        return $next($request);
    }
}
