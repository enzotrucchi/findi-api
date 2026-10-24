<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige ser admin de la organización seleccionada. Va después de OrganizacionActiva.
 */
class EsAdminOrganizacion
{
    public function handle(Request $request, Closure $next): Response
    {
        $organizacion = $request->attributes->get('organizacion');

        if (! $organizacion?->pivot->es_admin) {
            return response()->json([
                'ok' => false,
                'status' => 'FORBIDDEN',
                'message' => 'Solo los administradores pueden hacer esto.',
            ], 403);
        }

        return $next($request);
    }
}
