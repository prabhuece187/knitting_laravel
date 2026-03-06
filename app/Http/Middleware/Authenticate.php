<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
         // API requests get 401, no redirect
        if ($request->expectsJson()) {
            return null;
        }

        // Web requests can go to login page (if you have one)
        return '/login';
    }
}
