<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Allow CORS headers
        $response->header("Access-Control-Allow-Origin", "*");
        $response->header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
        $response->header("Access-Control-Allow-Headers", "Content-Type, Authorization");

        // Handle preflight OPTIONS request
        if ($request->isMethod("OPTIONS")) {
            return response("", 200)
                ->header("Access-Control-Allow-Origin", "*")
                ->header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS")
                ->header("Access-Control-Allow-Headers", "Content-Type, Authorization");
        }

        return $response;
    }
}
