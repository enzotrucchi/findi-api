<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Organizacion;
use App\Services\OrganizacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OrganizacionController extends Controller
{
    public function __construct(private OrganizacionService $organizacionService) {}

    /**
     * Organizaciones activas del usuario autenticado.
     */
    public function obtenerColeccion(Request $request): JsonResponse
    {
        $organizaciones = $request->user()
            ->organizaciones()
            ->wherePivot('activo', true)
            ->orderBy('nombre')
            ->get();

        return ApiResponse::exito($organizaciones, 'Organizaciones obtenidas exitosamente');
    }

    /**
     * Una organización de la que el usuario es miembro activo.
     */
    public function obtener(Request $request, int $id): JsonResponse
    {
        $organizacion = $request->user()
            ->organizaciones()
            ->wherePivot('activo', true)
            ->where('organizaciones.id', $id)
            ->first();

        if (! $organizacion) {
            return ApiResponse::noEncontrado('Organización no encontrada');
        }

        return ApiResponse::exito($organizacion, 'Organización obtenida exitosamente');
    }

    /**
     * Actualizar la organización seleccionada. Solo se puede cambiar el nombre:
     * prueba, vencimiento y habilitación los maneja la facturación.
     */
    public function actualizar(Request $request, int $id): JsonResponse
    {
        $organizacion = $this->organizacionSeleccionada($request);

        if ($organizacion->id !== $id) {
            return ApiResponse::error('Solo podés actualizar la organización seleccionada', 403);
        }

        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        try {
            $organizacion = $this->organizacionService->actualizarNombre($organizacion, $datos['nombre']);

            return ApiResponse::exito($organizacion, 'Organización actualizada exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::errorInterno('Error al actualizar organización', $e);
        }
    }

    public function obtenerCodigoAcceso(Request $request): JsonResponse
    {
        return ApiResponse::exito(
            ['codigo_acceso' => $this->organizacionSeleccionada($request)->codigo_acceso],
            'Código de acceso obtenido exitosamente'
        );
    }

    public function regenerarCodigoAcceso(Request $request): JsonResponse
    {
        try {
            $organizacion = $this->organizacionService->regenerarCodigoAcceso($this->organizacionSeleccionada($request)->id);

            return ApiResponse::exito(
                ['codigo_acceso' => $organizacion->codigo_acceso],
                'Código de acceso regenerado exitosamente'
            );
        } catch (\Exception $e) {
            return ApiResponse::errorInterno('Error al regenerar código de acceso', $e);
        }
    }

    public function actualizarCodigoAcceso(Request $request): JsonResponse
    {
        $request->validate([
            'codigo_acceso' => 'required|string|size:6',
        ]);

        try {
            $organizacion = $this->organizacionService->actualizarCodigoAcceso(
                $this->organizacionSeleccionada($request)->id,
                $request->input('codigo_acceso')
            );

            return ApiResponse::exito(
                ['codigo_acceso' => $organizacion->codigo_acceso],
                'Código de acceso actualizado exitosamente'
            );
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::errorInterno('Error al actualizar código de acceso', $e);
        }
    }

    /**
     * La organización que validó el middleware org.activa.
     */
    private function organizacionSeleccionada(Request $request): Organizacion
    {
        return $request->attributes->get('organizacion');
    }
}
