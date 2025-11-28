<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Proveedor\ActualizarProveedorDTO;
use App\DTOs\Proveedor\CrearProveedorDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedor\ProveedorRequest;
use App\Http\Responses\ApiResponse;
use App\Services\ProveedorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Controlador de Proveedores
 * 
 * Maneja las peticiones HTTP relacionadas con proveedores.
 * Solo orquesta, delega la lógica de negocio al Service.
 */
class ProveedorController extends Controller
{
    /**
     * Constructor.
     *
     * @param ProveedorService $proveedorService
     */
    public function __construct(
        private ProveedorService $proveedorService
    ) {}

    /**
     * Obtener colección de proveedores.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function obtenerColeccion(Request $request): JsonResponse
    {
        try {
            $soloActivos = $request->query('activos', false);
            $termino = $request->query('busqueda');

            if ($termino) {
                $proveedores = $this->proveedorService->buscar($termino);
            } else {
                $proveedores = $this->proveedorService->obtenerColeccion((bool) $soloActivos);
            }

            $datos = $proveedores->map(fn($dto) => $dto->aArray());

            return ApiResponse::exito(
                $datos,
                'Proveedores obtenidos exitosamente'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Error al obtener proveedores: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener un proveedor específico.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function obtener(int $id): JsonResponse
    {
        try {
            $proveedor = $this->proveedorService->obtenerPorId($id);

            if (!$proveedor) {
                return ApiResponse::noEncontrado('Proveedor no encontrado');
            }

            return ApiResponse::exito(
                $proveedor->aArray(),
                'Proveedor obtenido exitosamente'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Error al obtener proveedor: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Crear un nuevo proveedor.
     *
     * @param ProveedorRequest $request
     * @return JsonResponse
     */
    public function crear(ProveedorRequest $request): JsonResponse
    {
        try {
            $dto = CrearProveedorDTO::desdeArray($request->validated());
            $proveedor = $this->proveedorService->crear($dto);

            return ApiResponse::creado(
                $proveedor->aArray(),
                'Proveedor creado exitosamente'
            );
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Error al crear proveedor: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Actualizar un proveedor existente.
     *
     * @param ProveedorRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function actualizar(ProveedorRequest $request, int $id): JsonResponse
    {
        try {
            $dto = ActualizarProveedorDTO::desdeArray($request->validated());
            $proveedor = $this->proveedorService->actualizar($id, $dto);

            if (!$proveedor) {
                return ApiResponse::noEncontrado('Proveedor no encontrado');
            }

            return ApiResponse::exito(
                $proveedor->aArray(),
                'Proveedor actualizado exitosamente'
            );
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Error al actualizar proveedor: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Eliminar un proveedor.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function eliminar(int $id): JsonResponse
    {
        try {
            $eliminado = $this->proveedorService->eliminar($id);

            if (!$eliminado) {
                return ApiResponse::noEncontrado('Proveedor no encontrado');
            }

            return ApiResponse::exito(
                null,
                'Proveedor eliminado exitosamente'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Error al eliminar proveedor: ' . $e->getMessage(),
                500
            );
        }
    }
}
