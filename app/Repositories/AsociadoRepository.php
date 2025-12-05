<?php

namespace App\Repositories;

use App\Models\Asociado;
use App\Repositories\Contracts\AsociadoRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Implementación del repositorio de Asociados.
 *
 * Acá se centraliza el filtrado por organización seleccionada.
 * IMPORTANTE: no usar Asociado::... directo en otros lados
 * si necesitás respetar el contexto de organización.
 */
class AsociadoRepository implements AsociadoRepositoryInterface
{
    /**
     * Query base de asociados ligados a la organización seleccionada
     * del usuario autenticado.
     *
     * - Filtra por organizacion_seleccionada_id del usuario.
     * - Exige que la relación en el pivot esté activa (asociado_organizacion.activo = true).
     *
     * @return Builder
     */
    private function queryPorOrganizacionSeleccionada(): Builder
    {
        /** @var \App\Models\Asociado|null $user */
        $user = Auth::guard('sanctum')->user() ?? Auth::user();

        if (! $user instanceof Asociado) {
            abort(401, 'No autenticado.');
        }

        $orgId = $user->organizacion_seleccionada_id;

        if (! $orgId) {
            abort(403, 'No hay organización seleccionada.');
        }

        return Asociado::whereHas('organizaciones', function (Builder $q) use ($orgId) {
            $q->where('organizacion_id', $orgId)
                ->where('asociado_organizacion.activo', true); // membresía activa en esa organización
        });
    }

    /**
     * Obtener todos los asociados de la organización seleccionada.
     *
     * @return Collection<int,Asociado>
     */
    public function obtenerColeccion(): Collection
    {
        return $this->queryPorOrganizacionSeleccionada()
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener asociados activos (flag "activo" en la tabla asociados)
     * de la organización seleccionada.
     *
     * @return Collection<int,Asociado>
     */
    public function obtenerActivos(): Collection
    {
        return $this->queryPorOrganizacionSeleccionada()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener asociados administradores de la organización seleccionada.
     *
     * @return Collection<int,Asociado>
     */
    public function obtenerAdministradores(): Collection
    {
        return $this->queryPorOrganizacionSeleccionada()
            ->where('activo', true)
            ->whereHas('organizaciones', function (Builder $q) {
                $q->where('asociado_organizacion.es_admin', true);
            })
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener un asociado por ID dentro de la organización seleccionada.
     *
     * @param  int  $id
     * @return Asociado|null
     */
    public function obtenerPorId(int $id): ?Asociado
    {
        return $this->queryPorOrganizacionSeleccionada()
            ->where('id', $id)
            ->first();
    }

    /**
     * Crear un nuevo asociado (sin asignar organización).
     * La vinculación a organizaciones se hace en otro servicio.
     *
     * @param  array<string,mixed>  $datos
     * @return Asociado
     */
    public function crear(array $datos): Asociado
    {
        return Asociado::create($datos);
    }

    /**
     * Actualizar un asociado existente (respetando organización seleccionada).
     *
     * @param  int  $id
     * @param  array<string,mixed>  $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool
    {
        $asociado = $this->obtenerPorId($id);

        if (! $asociado) {
            return false;
        }

        return $asociado->update($datos);
    }

    /**
     * Eliminar un asociado (respetando organización seleccionada).
     *
     * @param  int  $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        $asociado = $this->obtenerPorId($id);

        if (! $asociado) {
            return false;
        }

        return $asociado->delete();
    }

    /**
     * Buscar asociados por término dentro de la organización seleccionada.
     *
     * @param  string  $termino
     * @return Collection<int,Asociado>
     */
    public function buscar(string $termino): Collection
    {
        return $this->queryPorOrganizacionSeleccionada()
            ->where(function (Builder $q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('email', 'like', "%{$termino}%")
                    ->orWhere('telefono', 'like', "%{$termino}%");
            })
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Verificar si un email ya existe en TODO el sistema (global, sin organización).
     *
     * @param  string     $email
     * @param  int|null   $excluirId
     * @return bool
     */
    public function existeEmail(string $email, ?int $excluirId = null): bool
    {
        $query = Asociado::query()
            ->where('email', $email);

        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }

    /**
     * Obtener asociados por múltiples IDs dentro de la organización seleccionada.
     *
     * @param  array<int>  $ids
     * @return Collection<int,Asociado>
     */
    public function obtenerPorIds(array $ids): Collection
    {
        return $this->queryPorOrganizacionSeleccionada()
            ->whereIn('id', $ids)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener estadísticas de asociados dentro de la organización seleccionada.
     *
     * @return array<string,mixed>
     */
    public function obtenerEstadisticas(): array
    {
        $queryBase = $this->queryPorOrganizacionSeleccionada();

        $total   = (clone $queryBase)->count();
        $activos = (clone $queryBase)->where('activo', true)->count();

        return [
            'asociados_totales' => $total,
            'asociados_activos' => $activos,
        ];
    }

    /**
     * Verificar si existe un asociado por ID dentro de la organización seleccionada.
     *
     * @param  int  $id
     * @return bool
     */
    public function existePorId(int $id): bool
    {
        return $this->queryPorOrganizacionSeleccionada()
            ->where('id', $id)
            ->exists();
    }

    /**
     * Contar total de asociados dentro de la organización seleccionada.
     *
     * @param  bool  $soloActivos
     * @return int
     */
    public function contarColeccion(bool $soloActivos = false): int
    {
        $query = $this->queryPorOrganizacionSeleccionada();

        // if ($soloActivos) {
        //     $query->where('activo', true);
        // }

        return $query->count();
    }
}
