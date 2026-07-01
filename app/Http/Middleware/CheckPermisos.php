<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermisos
{
    public function handle(Request $request, Closure $next, $modulo)
    {
        if (auth()->check()) {
            if (!auth()->user()->tienePermiso($modulo)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'No tienes permisos para acceder a este módulo.'], 403);
                }
                abort(403, 'No tienes permisos para acceder a este módulo.');
            }
        }
        return $next($request);
    }
}
