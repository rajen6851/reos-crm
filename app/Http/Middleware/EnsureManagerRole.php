<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureManagerRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isManager()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Manager role is required for these API endpoints.',
            ], 403);
        }

        return $next($request);
    }
}
