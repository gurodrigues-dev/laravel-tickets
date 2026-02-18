<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $request->headers->set('Content-Type', 'application/json');
        }

        return $next($request);
    }
}
