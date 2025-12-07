<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request)
    {
        // Para rutas de API o requests que esperan JSON,
        // devolvemos 401 en vez de redirigir a /login.
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }
    }
}
