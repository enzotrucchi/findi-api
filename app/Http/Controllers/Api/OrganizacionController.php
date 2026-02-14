<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\OrganizacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\DTOs\Organizacion\OrganizacionDTO;
use App\DTOs\Organizacion\FiltroOrganizacionDTO;

class OrganizacionController extends Controller
{
    public function __construct(private OrganizacionService $organizacionService) {}

    public function obtenerColeccion(Request $request): JsonResponse
    {
        try {
            $filtroDTO = new FiltroOrganizacionDTO();
            $filtroDTO->setSoloPrueba($this->normalizarBooleano($request->query('prueba', $request->query('es_prueba'))));
            $filtroDTO->setSoloProduccion($this->normalizarBooleano($request->query('produccion')));
            $filtroDTO->setSoloHabilitadas($this->normalizarBooleano($request->query('activos', $request->query('habilitadas'))));
            $filtroDTO->setSearch($request->query('search', $request->query('q', $request->query('busqueda'))));

            $organizaciones = $this->organizacionService->obtenerColeccion($filtroDTO);

            return ApiResponse::exito($organizaciones, 'Organizaciones obtenidas exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener organizaciones: ' . $e->getMessage(), 500);
        }
    }

    public function obtener(int $id): JsonResponse
    {
        try {
            $organizacion = $this->organizacionService->obtenerPorId($id);

            if (!$organizacion) {
                return ApiResponse::noEncontrado('Organización no encontrada');
            }

            return ApiResponse::exito($organizacion, 'Organización obtenida exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener organización: ' . $e->getMessage(), 500);
        }
    }

    public function crear(Request $request): JsonResponse
    {
        try {
            $dto = OrganizacionDTO::desdeArray($request->all());
            $organizacion = $this->organizacionService->crear($dto);

            return ApiResponse::creado($organizacion, 'Organización creada exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear organización: ' . $e->getMessage(), 500);
        }
    }

    public function actualizar(Request $request, int $id): JsonResponse
    {
        try {
            // Verificar que el usuario autenticado es admin de esta organización
            $usuario = $request->user();
            $esAdmin = $usuario->organizaciones()
                ->where('organizacion_id', $id)
                ->wherePivot('es_admin', true)
                ->exists();

            if (!$esAdmin) {
                return ApiResponse::error('No tienes permisos para actualizar esta organización', 403);
            }

            $dto = OrganizacionDTO::desdeArray($request->all());
            $organizacion = $this->organizacionService->actualizar($id, $dto);

            if (!$organizacion) {
                return ApiResponse::noEncontrado('Organización no encontrada');
            }

            return ApiResponse::exito($organizacion, 'Organización actualizada exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar organización: ' . $e->getMessage(), 500);
        }
    }

    public function eliminar(int $id): JsonResponse
    {
        try {
            $eliminado = $this->organizacionService->eliminar($id);

            if (!$eliminado) {
                return ApiResponse::noEncontrado('Organización no encontrada');
            }

            return ApiResponse::exito(null, 'Organización eliminada exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar organización: ' . $e->getMessage(), 500);
        }
    }

    public function buscar(Request $request): JsonResponse
    {
        try {
            $termino = $request->query('q') ?? $request->query('busqueda');

            if (!$termino) {
                return ApiResponse::error('Falta término de búsqueda', 400);
            }

            $resultados = $this->organizacionService->buscar($termino);
            return ApiResponse::exito($resultados, 'Búsqueda completada');
        } catch (\Exception $e) {
            return ApiResponse::error('Error en la búsqueda: ' . $e->getMessage(), 500);
        }
    }

    public function contar(Request $request): JsonResponse
    {
        try {
            $filtroDTO = new FiltroOrganizacionDTO();
            $filtroDTO->setSoloPrueba($this->normalizarBooleano($request->query('prueba', $request->query('es_prueba'))));
            $filtroDTO->setSoloProduccion($this->normalizarBooleano($request->query('produccion')));
            $filtroDTO->setSoloHabilitadas($this->normalizarBooleano($request->query('activos', $request->query('habilitadas'))));
            $filtroDTO->setSearch($request->query('search', $request->query('q', $request->query('busqueda'))));

            $cantidad = $this->organizacionService->contar($filtroDTO);
            return ApiResponse::exito(['cantidad' => $cantidad], 'Conteo realizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al contar organizaciones: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerPorIds(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $coleccion = $this->organizacionService->obtenerPorIds((array) $ids);
            return ApiResponse::exito($coleccion, 'Organizaciones obtenidas por ids');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener por ids: ' . $e->getMessage(), 500);
        }
    }

    public function existe(int $id): JsonResponse
    {
        try {
            $existe = $this->organizacionService->existePorId($id);
            return ApiResponse::exito(['existe' => $existe], 'Verificación completada');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al verificar existencia: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerCodigoAcceso(Request $request): JsonResponse
    {
        try {
            $usuario = $request->user();
            $orgId = $usuario->organizacion_seleccionada_id;

            if (!$orgId) {
                return ApiResponse::error('No tienes una organización seleccionada', 400);
            }

            // Verificar que es admin
            $esAdmin = $usuario->organizaciones()
                ->where('organizacion_id', $orgId)
                ->wherePivot('es_admin', true)
                ->exists();

            if (!$esAdmin) {
                return ApiResponse::error('No tienes permisos para ver el código de acceso', 403);
            }

            $organizacion = $this->organizacionService->obtenerPorId($orgId);

            if (!$organizacion) {
                return ApiResponse::noEncontrado('Organización no encontrada');
            }

            return ApiResponse::exito(
                ['codigo_acceso' => $organizacion->codigo_acceso],
                'Código de acceso obtenido exitosamente'
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener código de acceso: ' . $e->getMessage(), 500);
        }
    }

    public function regenerarCodigoAcceso(Request $request): JsonResponse
    {
        try {
            $usuario = $request->user();
            $orgId = $usuario->organizacion_seleccionada_id;

            if (!$orgId) {
                return ApiResponse::error('No tienes una organización seleccionada', 400);
            }

            // Verificar que es admin
            $esAdmin = $usuario->organizaciones()
                ->where('organizacion_id', $orgId)
                ->wherePivot('es_admin', true)
                ->exists();

            if (!$esAdmin) {
                return ApiResponse::error('No tienes permisos para regenerar el código de acceso', 403);
            }

            $organizacion = $this->organizacionService->regenerarCodigoAcceso($orgId);

            if (!$organizacion) {
                return ApiResponse::noEncontrado('Organización no encontrada');
            }

            return ApiResponse::exito(
                ['codigo_acceso' => $organizacion->codigo_acceso],
                'Código de acceso regenerado exitosamente'
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Error al regenerar código de acceso: ' . $e->getMessage(), 500);
        }
    }

    public function actualizarCodigoAcceso(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'codigo_acceso' => 'required|string|size:6',
            ]);

            $usuario = $request->user();
            $orgId = $usuario->organizacion_seleccionada_id;

            if (!$orgId) {
                return ApiResponse::error('No tienes una organización seleccionada', 400);
            }

            // Verificar que es admin
            $esAdmin = $usuario->organizaciones()
                ->where('organizacion_id', $orgId)
                ->wherePivot('es_admin', true)
                ->exists();

            if (!$esAdmin) {
                return ApiResponse::error('No tienes permisos para actualizar el código de acceso', 403);
            }

            $organizacion = $this->organizacionService->actualizarCodigoAcceso(
                $orgId,
                $request->input('codigo_acceso')
            );

            if (!$organizacion) {
                return ApiResponse::noEncontrado('Organización no encontrada');
            }

            return ApiResponse::exito(
                ['codigo_acceso' => $organizacion->codigo_acceso],
                'Código de acceso actualizado exitosamente'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar código de acceso: ' . $e->getMessage(), 500);
        }
    }

    private function normalizarBooleano(mixed $valor): ?bool
    {
        if ($valor === null) {
            return null;
        }

        return filter_var($valor, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    }
}
