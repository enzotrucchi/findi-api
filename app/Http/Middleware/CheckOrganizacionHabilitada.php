<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\Organizacion;

class CheckOrganizacionHabilitada
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::user();

        // If there's no authenticated user, don't block here — let auth middleware handle it
        if (! $user) {
            return $next($request);
        }

        $orgId = $user->organizacion_seleccionada_id;

        // If user has no selected organization, allow request to continue
        if (! $orgId) {
            return $next($request);
        }

        /** @var Organizacion|null $org */
        $org = Organizacion::find($orgId);

        // If org not found, block (security) — could be allowed depending on policy
        if (! $org) {
            return response()->json([
                'ok' => false,
                'message' => 'La organización seleccionada no existe.',
            ], 404);
        }

        // If column habilitada is missing or true, allow. If explicitly false, block.
        if (isset($org->habilitada) && ! (bool) $org->habilitada) {
            return response()->json([
                'ok' => false,
                'message' => 'La organización seleccionada está deshabilitada. Contacta al administrador.',
                'organizacion' => [
                    'id' => $org->id,
                    'nombre' => $org->nombre,
                    'habilitada' => (bool) $org->habilitada,
                ],
            ], 403);
        }

        return $next($request);
    }
}
