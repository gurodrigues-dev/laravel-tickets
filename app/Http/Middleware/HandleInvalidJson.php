<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleInvalidJson
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*') && $request->isJson()) {
            try {
                json_decode($request->getContent());
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid JSON format',
                    ], 400);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON format',
                ], 400);
            }
        }

        return $next($request);
    }
}
