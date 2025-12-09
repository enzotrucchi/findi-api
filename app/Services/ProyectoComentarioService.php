<?php

namespace App\Services;

use App\Models\ProyectoComentarios;
use App\Models\Proyecto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Support\Facades\Auth;

/**
 * Servicio de Comentarios de Proyectos
 * 
 * Contiene toda la lógica de negocio relacionada con comentarios de proyectos.
 */
class ProyectoComentarioService
{
    public function __construct() {}

    /**
     * Obtener todos los comentarios de un proyecto.
     *
     * @param int $proyectoId
     * @return Collection
     */
    public function obtenerComentarios(int $proyectoId): Collection
    {
        // Verificar que el proyecto exista
        $proyecto = Proyecto::find($proyectoId);

        if (!$proyecto) {
            throw new InvalidArgumentException('Proyecto no encontrado.');
        }

        return ProyectoComentarios::where('proyecto_id', $proyectoId)
            ->with('asociado:id,nombre,email')
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($comentario) {
                return [
                    'id' => $comentario->id,
                    'proyecto_id' => $comentario->proyecto_id,
                    'asociado_id' => $comentario->asociado_id,
                    'asociado' => $comentario->asociado ? [
                        'id' => $comentario->asociado->id,
                        'nombre' => $comentario->asociado->nombre,
                        'email' => $comentario->asociado->email,
                    ] : null,
                    'detalle' => $comentario->detalle,
                    'fecha' => $comentario->fecha->format('Y-m-d H:i:s'),
                    'created_at' => $comentario->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $comentario->updated_at?->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * Agregar un nuevo comentario a un proyecto.
     *
     * @param int $proyectoId
     * @param array $datos
     * @return ProyectoComentarios
     * @throws InvalidArgumentException
     */
    public function agregarComentario(int $proyectoId, array $datos): ProyectoComentarios
    {
        // Verificar que el proyecto exista
        $proyecto = Proyecto::find($proyectoId);

        if (!$proyecto) {
            throw new InvalidArgumentException('Proyecto no encontrado.');
        }

        // Validaciones de negocio
        if (empty($datos['detalle'])) {
            throw new InvalidArgumentException('El detalle del comentario es obligatorio.');
        }

        /** @var \App\Models\Asociado $user */
        $user = Auth::user();

        return DB::transaction(function () use ($proyectoId, $datos, $user) {
            return ProyectoComentarios::create([
                'proyecto_id' => $proyectoId,
                'asociado_id' => $user->id,
                'detalle' => $datos['detalle'],
                'fecha' => $datos['fecha'] ?? now(),
            ]);
        });
    }

    /**
     * Actualizar un comentario existente.
     *
     * @param int $proyectoId
     * @param int $comentarioId
     * @param array $datos
     * @return ProyectoComentarios|null
     * @throws InvalidArgumentException
     */
    public function actualizarComentario(int $proyectoId, int $comentarioId, array $datos): ?ProyectoComentarios
    {
        $comentario = ProyectoComentarios::where('id', $comentarioId)
            ->where('proyecto_id', $proyectoId)
            ->first();

        if (!$comentario) {
            return null;
        }

        // Validaciones de negocio
        if (isset($datos['detalle']) && empty($datos['detalle'])) {
            throw new InvalidArgumentException('El detalle del comentario no puede estar vacío.');
        }

        /** @var \App\Models\Asociado $user */
        $user = Auth::user();

        // Solo el autor del comentario puede actualizarlo
        if ($comentario->asociado_id !== $user->id) {
            throw new InvalidArgumentException('No tienes permisos para actualizar este comentario.');
        }

        return DB::transaction(function () use ($comentario, $datos) {
            if (isset($datos['detalle'])) {
                $comentario->detalle = $datos['detalle'];
            }

            if (isset($datos['fecha'])) {
                $comentario->fecha = $datos['fecha'];
            }

            $comentario->save();

            return $comentario;
        });
    }

    /**
     * Eliminar un comentario.
     *
     * @param int $proyectoId
     * @param int $comentarioId
     * @return bool
     * @throws InvalidArgumentException
     */
    public function eliminarComentario(int $proyectoId, int $comentarioId): bool
    {
        $comentario = ProyectoComentarios::where('id', $comentarioId)
            ->where('proyecto_id', $proyectoId)
            ->first();

        if (!$comentario) {
            return false;
        }

        /** @var \App\Models\Asociado $user */
        $user = Auth::user();

        // Solo el autor del comentario puede eliminarlo
        if ($comentario->asociado_id !== $user->id) {
            throw new InvalidArgumentException('No tienes permisos para eliminar este comentario.');
        }

        return DB::transaction(function () use ($comentario) {
            return $comentario->delete();
        });
    }
}
