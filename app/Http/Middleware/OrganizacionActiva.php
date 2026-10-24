<?php

namespace App\Http\Middleware;

use App\Models\Asociado;
use App\Models\Organizacion;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige que el usuario opere sobre una organización a la que pertenece y que puede usar la app.
 *
 * Deja la organización (con el pivot de la membresía) en el atributo "organizacion" del request.
 */
class OrganizacionActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Asociado $usuario */
        $usuario = $request->user();

        if (! $usuario->organizacion_seleccionada_id) {
            return $this->rechazar('NO_ORG_SELECTED', 'No hay organización seleccionada.');
        }

        /** @var Organizacion|null $organizacion */
        $organizacion = $usuario->organizaciones()
            ->where('organizaciones.id', $usuario->organizacion_seleccionada_id)
            ->wherePivot('activo', true)
            ->first();

        if (! $organizacion) {
            return $this->rechazar('NOT_A_MEMBER', 'No pertenecés a la organización seleccionada.');
        }

        if (! $organizacion->habilitada) {
            return $this->rechazar('ORG_DISABLED', 'La organización está deshabilitada. Contactanos a hola@findiapp.com');
        }

        if ($organizacion->es_prueba && $organizacion->fecha_fin_prueba
            && Carbon::parse($organizacion->fecha_fin_prueba)->endOfDay()->isPast()) {
            return $this->rechazar('TRIAL_EXPIRED', 'Terminó el período de prueba. Contactanos a hola@findiapp.com');
        }

        if (! $organizacion->pivot->es_admin && ! $organizacion->pivot->primer_login_completado) {
            return $this->rechazar('NEEDS_ACCESS_CODE', 'Ingresá el código de acceso de la organización.');
        }

        $request->attributes->set('organizacion', $organizacion);

        return $next($request);
    }

    private function rechazar(string $status, string $mensaje): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'status' => $status,
            'message' => $mensaje,
        ], 403);
    }
}
