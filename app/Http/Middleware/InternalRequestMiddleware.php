<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Validate requests that are coming from the internal system
 */
class InternalRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if(!$request->hasHeader('x-secret')){
            abort(404);
        }

        if($request->header('x-secret') != env('INTERNAL_X_SECRET')){
            abort(404);
        }

        return $next($request);
    }
}
