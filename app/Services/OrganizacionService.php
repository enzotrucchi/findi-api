<?php

namespace App\Services;

use App\DTOs\Organizacion\FiltroOrganizacionDTO;
use App\DTOs\Organizacion\OrganizacionDTO;
use App\Models\Organizacion;
use Illuminate\Database\Eloquent\Builder;
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
     * @param FiltroOrganizacionDTO $filtroDTO
     * @return Collection
     */
    public function obtenerColeccion(FiltroOrganizacionDTO $filtroDTO): Collection
    {
        $query = $this->queryBase();
        $this->aplicarFiltros($query, $filtroDTO);

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
        // if ($this->existeNombre($dto->nombre)) {
        //     throw new InvalidArgumentException('El nombre de organización ya está registrado.');
        // }

        // Validar fecha de fin de prueba
        if ($dto->esPrueba && $dto->fechaFinPrueba === null) {
            throw new InvalidArgumentException('Las organizaciones de prueba deben tener una fecha de fin de prueba.');
        }

        $datos = $this->prepararDatos($dto);
        $datos['codigo_acceso'] = $this->generarCodigoAcceso();

        return Organizacion::create($datos);
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
        // if ($nombreNormalizado !== $organizacion->nombre && $this->existeNombre($nombreNormalizado, $id)) {
        //     throw new InvalidArgumentException('El nombre de organización ya está registrado.');
        // }

        $organizacion->update($this->prepararDatos($dto, $organizacion));

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
        $query = $this->queryBase();
        $this->aplicarBusqueda($query, $termino);

        return $query->orderBy('nombre', 'asc')->get();
    }

    /**
     * Obtener organizaciones por múltiples IDs.
     *
     * @param array<int> $ids
     * @return Collection
     */
    public function obtenerPorIds(array $ids): Collection
    {
        return $this->queryBase()->whereIn('id', $ids)->get();
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
    public function contar(FiltroOrganizacionDTO $filtroDTO): int
    {
        $query = $this->queryBase();
        $this->aplicarFiltros($query, $filtroDTO);

        return $query->count();
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
        $query = $this->queryBase()->where('nombre', $this->normalizarNombre($nombre));
        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }

    private function queryBase(): Builder
    {
        return Organizacion::query();
    }

    private function aplicarFiltros(Builder $query, FiltroOrganizacionDTO $filtroDTO): void
    {
        if ($filtroDTO->getSoloPrueba()) {
            $query->where('es_prueba', true);
        } elseif ($filtroDTO->getSoloProduccion()) {
            $query->where('es_prueba', false);
        }

        if ($filtroDTO->getSoloHabilitadas() !== null) {
            $query->where('habilitada', $filtroDTO->getSoloHabilitadas());
        }

        $this->aplicarBusqueda($query, $filtroDTO->getSearch());
    }

    private function aplicarBusqueda(Builder $query, ?string $termino): void
    {
        if ($termino !== null && $termino !== '') {
            $query->where('nombre', 'like', '%' . $termino . '%');
        }
    }

    private function prepararDatos(OrganizacionDTO $dto, ?Organizacion $organizacion = null): array
    {
        return [
            'nombre' => $this->normalizarNombre($dto->nombre),
            'fecha_alta' => $dto->fechaAlta ?? $organizacion?->fecha_alta ?? now()->format('Y-m-d'),
            'es_prueba' => $dto->esPrueba ?? $organizacion?->es_prueba ?? false,
            'fecha_fin_prueba' => $dto->fechaFinPrueba ?? $organizacion?->fecha_fin_prueba,
        ];
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

    /**
     * Generar código de acceso alfanumérico de 6 caracteres.
     *
     * @return string
     */
    public function generarCodigoAcceso(): string
    {
        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $codigo = '';
        $longitudCaracteres = strlen($caracteres);

        for ($i = 0; $i < 6; $i++) {
            $codigo .= $caracteres[random_int(0, $longitudCaracteres - 1)];
        }

        return $codigo;
    }

    /**
     * Regenerar código de acceso para una organización.
     *
     * @param int $id
     * @return Organizacion|null
     */
    public function regenerarCodigoAcceso(int $id): ?Organizacion
    {
        $organizacion = Organizacion::find($id);

        if (!$organizacion) {
            return null;
        }

        $organizacion->update(['codigo_acceso' => $this->generarCodigoAcceso()]);

        return $organizacion->fresh();
    }

    /**
     * Actualizar código de acceso personalizado para una organización.
     *
     * @param int $id
     * @param string $codigo
     * @return Organizacion|null
     */
    public function actualizarCodigoAcceso(int $id, string $codigo): ?Organizacion
    {
        $organizacion = Organizacion::find($id);

        if (!$organizacion) {
            return null;
        }

        $codigoNormalizado = strtoupper(trim($codigo));

        if (strlen($codigoNormalizado) !== 6 || !ctype_alnum($codigoNormalizado)) {
            throw new \InvalidArgumentException('El código debe tener exactamente 6 caracteres alfanuméricos.');
        }

        $organizacion->update(['codigo_acceso' => $codigoNormalizado]);

        return $organizacion->fresh();
    }
}
