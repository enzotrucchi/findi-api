<?php

namespace App\Services\Traits;

use App\Models\Asociado;
use Illuminate\Support\Facades\Auth;

/**
 * Trait para obtener la organización seleccionada del usuario autenticado.
 * 
 * Centraliza la lógica de validación y obtención del organizacion_seleccionada_id
 * en los servicios que requieren filtrar por organización.
 */
trait ObtenerOrganizacionSeleccionada
{
    /**
     * Obtener el ID de la organización seleccionada del usuario autenticado.
     * 
     * @return int
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function obtenerOrganizacionId(): int
    {
        /** @var \App\Models\Asociado|null $user */
        $user = Auth::guard('sanctum')->user() ?? Auth::user();

        if (!$user instanceof Asociado) {
            abort(401, 'No autenticado.');
        }

        $orgId = $user->organizacion_seleccionada_id;

        if (!$orgId) {
            abort(403, 'No hay organización seleccionada.');
        }

        return $orgId;
    }

    /**
     * Obtener el usuario autenticado como Asociado.
     * 
     * @return Asociado
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function obtenerUsuarioAutenticado(): Asociado
    {
        /** @var \App\Models\Asociado|null $user */
        $user = Auth::guard('sanctum')->user() ?? Auth::user();

        if (!$user instanceof Asociado) {
            abort(401, 'No autenticado.');
        }

        return $user;
    }
}
