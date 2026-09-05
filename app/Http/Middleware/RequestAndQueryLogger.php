<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestAndQueryLogger
{
    /**
     * Handle an incoming request and log HTTP request details, response execution time, and SQL queries.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $user = $request->user();
        $userId = $user ? $user->id : 'Guest';
        $userEmail = $user ? $user->email : 'Guest';

        // Filter out sensitive field data from logs
        $filteredInput = $request->except(['password', 'password_confirmation', 'secret', '_token', 'card_number', 'cvv']);

        Log::info(sprintf(
            '[HTTP REQ] %s %s | User: #%s (%s) | IP: %s | Input: %s',
            $request->method(),
            $request->fullUrl(),
            $userId,
            $userEmail,
            $request->ip(),
            json_encode($filteredInput, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        ));

        // Enable SQL query logging during request execution in debug mode
        if (config('app.debug')) {
            DB::listen(function ($query) {
                $sql = $query->sql;
                foreach ($query->bindings as $binding) {
                    $value = is_numeric($binding) ? $binding : "'".addslashes((string)$binding)."'";
                    $sql = preg_replace('/\?/', $value, $sql, 1);
                }
                Log::debug(sprintf('[SQL QUERY] %s | Time: %.2f ms', $sql, $query->time));
            });
        }

        $response = $next($request);

        $duration = number_format((microtime(true) - $startTime) * 1000, 2);

        Log::info(sprintf(
            '[HTTP RES] %s /%s -> Status: %d | Execution: %s ms',
            $request->method(),
            ltrim($request->path(), '/'),
            $response->getStatusCode(),
            $duration
        ));

        return $response;
    }
}
