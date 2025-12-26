<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organizacion;
use Illuminate\Http\JsonResponse;

class CambiarOrganizacionSeleccionadaController extends Controller
{
    /**
     * POST /api/organizaciones/{organizacion}/seleccionar
     */
    public function __invoke(Organizacion $organizacion): JsonResponse
    {
        /** @var \App\Models\Asociado|null $user */
        $user = auth('sanctum')->user();

        if (! $user) {
            abort(401, 'No autenticado');
        }

        // ¿Pertenece y está activa esa relación?
        $pertenece = $user->organizaciones()
            ->where('organizacion_id', $organizacion->id)
            ->wherePivot('activo', true)
            ->exists();

        if (! $pertenece) {
            abort(403, 'No tenés acceso activo a esta organización.');
        }

        // Además verificamos que la organización esté habilitada (no permitir seleccionar
        // una org deshabilitada).
        if (isset($organizacion->habilitada) && ! (bool) $organizacion->habilitada) {
            abort(403, 'La organización seleccionada está deshabilitada.');
        }

        $user->organizacion_seleccionada_id = $organizacion->id;
        $user->save();

        return response()->json([
            'message'                      => 'Organización seleccionada correctamente.',
            'organizacion_seleccionada_id' => $user->organizacion_seleccionada_id,
        ]);
    }
}
