<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticatedToApp
{
    /**
     * Redirect authenticated users away from public pages to the app.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            return redirect()->route('design.templates.explore');
        }

        return $next($request);
    }
}
