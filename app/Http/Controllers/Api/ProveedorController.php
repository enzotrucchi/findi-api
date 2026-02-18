<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Proveedor\FiltroProveedorDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedor\ProveedorRequest;
use App\Http\Responses\ApiResponse;
use App\Services\ProveedorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\DTOs\Proveedor\ProveedorDTO;
use App\Models\Proveedor;

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
            $filtroDTO = new FiltroProveedorDTO();
            // $filtroDTO->setPagina(request()->input('pagina', 1));

            $proveedores = $this->proveedorService->obtenerColeccion($filtroDTO);

            return ApiResponse::exito(
                $proveedores,
                'Proveedores obtenidos exitosamente'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Error al obtener proveedores: ' . $e->getMessage(),
                500
            );
        }
    }

    public function obtenerMovimientos(int $id): JsonResponse
    {
        try {
            $tipo = request()->query('tipo');
            $movimientos = $this->proveedorService->obtenerMovimientos($id, $tipo);

            if ($movimientos === null) {
                return ApiResponse::noEncontrado('Proveedor no encontrado');
            }

            return ApiResponse::exito($movimientos, 'Movimientos del proveedor obtenidos exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener movimientos: ' . $e->getMessage(), 500);
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
            $dto = ProveedorDTO::desdeArray($request->validated());

            $proveedor = $this->proveedorService->crear($dto);

            return ApiResponse::creado(
                $proveedor,
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
            $dto = ProveedorDTO::desdeArray($request->validated());
            $proveedor = $this->proveedorService->actualizar($id, $dto);
            if (!$proveedor) {
                return ApiResponse::noEncontrado('Proveedor no encontrado');
            }

            return ApiResponse::exito(
                $proveedor,
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

            $proveedor = Proveedor::find($id);

            if (!$proveedor) {
                return ApiResponse::noEncontrado('Proveedor no encontrado');
            }

            if ($proveedor->movimientos()->exists()) {
                return ApiResponse::error(
                    'No se puede eliminar el proveedor porque tiene movimientos asociados.',
                    400
                );
            }

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
