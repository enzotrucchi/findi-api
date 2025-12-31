<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganizacionAcceso
{
    /**
     * Verificar que la organización tenga acceso a la aplicación.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado, dejar pasar (otros middleware se encargan)
        if (!$user) {
            return $next($request);
        }

        // Obtener la organización del usuario (asumiendo que User tiene relación con Organizacion)
        // Ajustar según tu estructura de datos
        $organizacion = $this->obtenerOrganizacionDelUsuario($request);

        if (!$organizacion) {
            return response()->json([
                'message' => 'No se encontró una organización asociada al usuario.',
            ], 403);
        }

        // Verificar si la organización tiene acceso
        if (!$organizacion->tieneAcceso()) {
            $mensaje = $this->obtenerMensajeBloqueo($organizacion);

            return response()->json([
                'message' => $mensaje,
                'organizacion' => [
                    'id' => $organizacion->id,
                    'nombre' => $organizacion->nombre,
                    'habilitada' => $organizacion->habilitada,
                    'fecha_vencimiento' => $organizacion->fecha_vencimiento?->format('Y-m-d'),
                    'es_prueba' => $organizacion->es_prueba,
                    'fecha_fin_prueba' => $organizacion->fecha_fin_prueba?->format('Y-m-d'),
                ],
            ], 403);
        }

        return $next($request);
    }

    /**
     * Obtener la organización del usuario actual.
     * Ajustar según la estructura de tu aplicación.
     *
     * @param Request $request
     * @return \App\Models\Organizacion|null
     */
    private function obtenerOrganizacionDelUsuario(Request $request)
    {
        $user = $request->user();

        // Opción 1: Si el usuario tiene un organizacion_id directamente
        if (isset($user->organizacion_id)) {
            return \App\Models\Organizacion::find($user->organizacion_id);
        }

        // Opción 2: Si la organización viene en el request (por ejemplo en el header o query)
        if ($request->has('organizacion_id')) {
            return \App\Models\Organizacion::find($request->input('organizacion_id'));
        }

        // Opción 3: Si tienes una relación directa en el modelo User
        if (method_exists($user, 'organizacion')) {
            return $user->organizacion;
        }

        return null;
    }

    /**
     * Obtener mensaje de bloqueo según el estado de la organización.
     *
     * @param \App\Models\Organizacion $organizacion
     * @return string
     */
    private function obtenerMensajeBloqueo($organizacion): string
    {
        if (!$organizacion->habilitada) {
            return 'Su organización está deshabilitada. Por favor, póngase en contacto con soporte.';
        }

        if ($organizacion->es_prueba && $organizacion->estaVencida()) {
            return 'Su período de prueba ha finalizado. Por favor, active una suscripción para continuar usando la aplicación.';
        }

        if (!$organizacion->es_prueba && $organizacion->estaVencida()) {
            return 'Su suscripción ha vencido. Por favor, realice el pago para continuar usando la aplicación.';
        }

        return 'Su organización no tiene acceso a la aplicación.';
    }
}
