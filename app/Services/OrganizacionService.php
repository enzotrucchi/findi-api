<?php

namespace App\Services;

use App\DTOs\Organizacion\OrganizacionDTO;
use App\Models\Organizacion;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Servicio de Organizaciones
 * 
 * Contiene toda la lógica de negocio relacionada con organizaciones.
 */
class OrganizacionService
{
    public function __construct() {}

    /**
     * Obtener todas las organizaciones.
     *
     * @param bool $soloPrueba
     * @param bool $soloProduccion
     * @return Collection
     */
    public function obtenerColeccion(bool $soloPrueba = false, bool $soloProduccion = false): Collection
    {
        $query = Organizacion::query();

        if ($soloPrueba) {
            $query->where('es_prueba', true);
        } elseif ($soloProduccion) {
            $query->where('es_prueba', false);
        }

        return $query->orderBy('nombre', 'asc')->get();
    }

    /**
     * Obtener una organización por ID.
     *
     * @param int $id
     * @return Organizacion|null
     */
    public function obtenerPorId(int $id): ?Organizacion
    {
        return Organizacion::find($id);
    }

    /**
     * Crear una nueva organización.
     *
     * @param OrganizacionDTO $dto
     * @return Organizacion
     * @throws InvalidArgumentException
     */
    public function crear(OrganizacionDTO $dto): Organizacion
    {
        // Validar que el nombre no exista
        if ($this->existeNombre($dto->nombre)) {
            throw new InvalidArgumentException('El nombre de organización ya está registrado.');
        }

        // Validar fecha de fin de prueba
        if ($dto->esPrueba && $dto->fechaFinPrueba === null) {
            throw new InvalidArgumentException('Las organizaciones de prueba deben tener una fecha de fin de prueba.');
        }

        return Organizacion::create([
            'nombre' => $this->normalizarNombre($dto->nombre),
            'fecha_alta' => $dto->fechaAlta ?? now()->format('Y-m-d'),
            'es_prueba' => $dto->esPrueba ?? false,
            'fecha_fin_prueba' => $dto->fechaFinPrueba,
        ]);
    }

    /**
     * Actualizar una organización existente.
     *
     * @param int $id
     * @param OrganizacionDTO $dto
     * @return Organizacion|null
     * @throws InvalidArgumentException
     */
    public function actualizar(int $id, OrganizacionDTO $dto): ?Organizacion
    {
        $organizacion = Organizacion::find($id);

        if (!$organizacion) {
            return null;
        }

        // Validar nombre si cambió
        $nombreNormalizado = $this->normalizarNombre($dto->nombre);
        if ($nombreNormalizado !== $organizacion->nombre && $this->existeNombre($nombreNormalizado, $id)) {
            throw new InvalidArgumentException('El nombre de organización ya está registrado.');
        }

        $organizacion->update([
            'nombre' => $nombreNormalizado,
            'fecha_alta' => $dto->fechaAlta ?? $organizacion->fecha_alta,
            'es_prueba' => $dto->esPrueba ?? $organizacion->es_prueba,
            'fecha_fin_prueba' => $dto->fechaFinPrueba ?? $organizacion->fecha_fin_prueba,
        ]);

        return $organizacion->fresh();
    }

    /**
     * Eliminar una organización.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        $organizacion = Organizacion::find($id);

        if (!$organizacion) {
            return false;
        }

        return $organizacion->delete();
    }

    /**
     * Buscar organizaciones por término.
     *
     * @param string $termino
     * @return Collection
     */
    public function buscar(string $termino): Collection
    {
        return Organizacion::where('nombre', 'like', '%' . $termino . '%')
            ->orderBy('nombre', 'asc')
            ->get();
    }

    /**
     * Obtener organizaciones por múltiples IDs.
     *
     * @param array<int> $ids
     * @return Collection
     */
    public function obtenerPorIds(array $ids): Collection
    {
        return Organizacion::whereIn('id', $ids)->get();
    }

    /**
     * Verificar si existe una organización.
     *
     * @param int $id
     * @return bool
     */
    public function existePorId(int $id): bool
    {
        return Organizacion::where('id', $id)->exists();
    }

    /**
     * Contar organizaciones.
     *
     * @return int
     */
    public function contar(): int
    {
        return Organizacion::count();
    }

    /**
     * Verificar si existe un nombre de organización.
     *
     * @param string $nombre
     * @param int|null $excluirId
     * @return bool
     */
    public function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $query = Organizacion::where('nombre', $this->normalizarNombre($nombre));
        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }

    /**
     * Normalizar nombre (capitalizar cada palabra).
     *
     * @param string $nombre
     * @return string
     */
    private function normalizarNombre(string $nombre): string
    {
        return ucwords(strtolower(trim($nombre)));
    }
}
