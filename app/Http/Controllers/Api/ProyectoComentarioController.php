<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ProyectoComentarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ProyectoComentarioController extends Controller
{
    public function __construct(private ProyectoComentarioService $proyectoComentarioService) {}

    /**
     * Obtener todos los comentarios de un proyecto
     */
    public function obtenerComentarios(int $id): JsonResponse
    {
        try {
            $comentarios = $this->proyectoComentarioService->obtenerComentarios($id);

            return ApiResponse::exito($comentarios, 'Comentarios obtenidos exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener comentarios: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Agregar un nuevo comentario a un proyecto
     */
    public function agregarComentario(Request $request, int $id): JsonResponse
    {
        try {
            $comentario = $this->proyectoComentarioService->agregarComentario($id, $request->all());

            return ApiResponse::creado([
                'id' => $comentario->id,
                'proyecto_id' => $comentario->proyecto_id,
                'asociado_id' => $comentario->asociado_id,
                'detalle' => $comentario->detalle,
                'fecha' => $comentario->fecha->format('Y-m-d H:i:s'),
                'created_at' => $comentario->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $comentario->updated_at?->format('Y-m-d H:i:s'),
            ], 'Comentario agregado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al agregar comentario: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar un comentario existente
     */
    public function actualizarComentario(Request $request, int $proyectoId, int $comentarioId): JsonResponse
    {
        try {
            $comentario = $this->proyectoComentarioService->actualizarComentario($proyectoId, $comentarioId, $request->all());

            if (!$comentario) {
                return ApiResponse::noEncontrado('Comentario no encontrado');
            }

            return ApiResponse::exito([
                'id' => $comentario->id,
                'proyecto_id' => $comentario->proyecto_id,
                'asociado_id' => $comentario->asociado_id,
                'detalle' => $comentario->detalle,
                'fecha' => $comentario->fecha->format('Y-m-d H:i:s'),
                'created_at' => $comentario->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $comentario->updated_at?->format('Y-m-d H:i:s'),
            ], 'Comentario actualizado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar comentario: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar un comentario
     */
    public function eliminarComentario(int $proyectoId, int $comentarioId): JsonResponse
    {
        try {
            $eliminado = $this->proyectoComentarioService->eliminarComentario($proyectoId, $comentarioId);

            if (!$eliminado) {
                return ApiResponse::noEncontrado('Comentario no encontrado');
            }

            return ApiResponse::exito(null, 'Comentario eliminado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar comentario: ' . $e->getMessage(), 500);
        }
    }
}
