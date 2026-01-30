<?php

namespace App\Services;

use App\DTOs\Lista\ListaDTO;
use App\DTOs\Lista\FiltroListaDTO;
use App\Models\Lista;
use App\Models\Asociado;
use App\Services\Traits\ObtenerOrganizacionSeleccionada;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Servicio de Listas
 * 
 * Contiene toda la lógica de negocio relacionada con listas de asociados.
 */
class ListaService
{
    use ObtenerOrganizacionSeleccionada;

    public function __construct() {}

    /**
     * Obtener colección paginada de listas con conteo de asociados.
     *
     * @param FiltroListaDTO $filtroDTO
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function obtenerColeccion(FiltroListaDTO $filtroDTO): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Lista::query()
            ->withCount('asociados')
            ->orderBy('nombre', 'asc');

        return $query->paginate(
            perPage: 10,
            columns: ['*'],
            pageName: 'pagina',
            page: $filtroDTO->getPagina()
        );
    }

    /**
     * Obtener todas las listas sin paginación (para selects/dropdowns).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerTodas(): \Illuminate\Database\Eloquent\Collection
    {
        return Lista::query()
            ->withCount('asociados')
            ->orderBy('nombre', 'asc')
            ->get();
    }

    /**
     * Obtener una lista por su ID.
     *
     * @param int $id
     * @return Lista|null
     */
    public function obtenerPorId(int $id): ?Lista
    {
        return Lista::with(['asociados'])->find($id);
    }

    /**
     * Crear una nueva lista.
     *
     * @param ListaDTO $dto
     * @return Lista
     * @throws InvalidArgumentException
     */
    public function crear(ListaDTO $dto): Lista
    {
        $orgId = $this->obtenerOrganizacionId();

        // Validar color hex si se proporciona
        if ($dto->color && !$this->validarColorHex($dto->color)) {
            throw new InvalidArgumentException('El color debe ser un código hexadecimal válido (#RRGGBB).', 422);
        }

        // Verificar unicidad del nombre en la organización
        $existente = Lista::where('organizacion_id', $orgId)
            ->where('nombre', $dto->nombre)
            ->exists();

        if ($existente) {
            throw new InvalidArgumentException('Ya existe una lista con ese nombre en esta organización.', 422);
        }

        $lista = Lista::create([
            'nombre' => trim($dto->nombre),
            'descripcion' => $dto->descripcion ? trim($dto->descripcion) : null,
            'color' => $dto->color,
            'organizacion_id' => $orgId,
        ]);

        return $lista->loadCount('asociados');
    }

    /**
     * Actualizar una lista existente.
     *
     * @param int $id
     * @param ListaDTO $dto
     * @return Lista
     * @throws InvalidArgumentException
     */
    public function actualizar(int $id, ListaDTO $dto): Lista
    {
        $lista = Lista::find($id);

        if (!$lista) {
            throw new InvalidArgumentException('Lista no encontrada.', 404);
        }

        $orgId = $this->obtenerOrganizacionId();

        // Validar color hex si se proporciona
        if ($dto->color && !$this->validarColorHex($dto->color)) {
            throw new InvalidArgumentException('El color debe ser un código hexadecimal válido (#RRGGBB).', 422);
        }

        // Verificar unicidad del nombre (excluyendo la lista actual)
        $existente = Lista::where('organizacion_id', $orgId)
            ->where('nombre', $dto->nombre)
            ->where('id', '!=', $id)
            ->exists();

        if ($existente) {
            throw new InvalidArgumentException('Ya existe una lista con ese nombre en esta organización.', 422);
        }

        $lista->update([
            'nombre' => trim($dto->nombre),
            'descripcion' => $dto->descripcion ? trim($dto->descripcion) : null,
            'color' => $dto->color,
        ]);

        return $lista->fresh()->loadCount('asociados');
    }

    /**
     * Eliminar una lista.
     *
     * @param int $id
     * @return bool
     * @throws InvalidArgumentException
     */
    public function eliminar(int $id): bool
    {
        $lista = Lista::find($id);

        if (!$lista) {
            throw new InvalidArgumentException('Lista no encontrada.', 404);
        }

        return $lista->delete();
    }

    /**
     * Obtener asociados de una lista.
     *
     * @param int $listaId
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws InvalidArgumentException
     */
    public function obtenerAsociadosPorLista(int $listaId): \Illuminate\Database\Eloquent\Collection
    {
        $lista = Lista::find($listaId);

        if (!$lista) {
            throw new InvalidArgumentException('Lista no encontrada.', 404);
        }

        return $lista->asociados()->orderBy('nombre', 'asc')->get();
    }

    /**
     * Agregar múltiples asociados a una lista.
     *
     * @param int $listaId
     * @param array<int> $asociadoIds
     * @return Lista
     * @throws InvalidArgumentException
     */
    public function agregarAsociados(int $listaId, array $asociadoIds): Lista
    {
        $lista = Lista::find($listaId);

        if (!$lista) {
            throw new InvalidArgumentException('Lista no encontrada.', 404);
        }

        $orgId = $this->obtenerOrganizacionId();

        // Validar que los asociados pertenezcan a la organización
        $asociadosValidos = Asociado::whereHas('organizaciones', function ($query) use ($orgId) {
            $query->where('organizacion_id', $orgId)
                ->where('asociado_organizacion.activo', true);
        })
            ->whereIn('id', $asociadoIds)
            ->pluck('id')
            ->toArray();

        if (count($asociadosValidos) !== count($asociadoIds)) {
            throw new InvalidArgumentException('Algunos asociados no pertenecen a esta organización.', 422);
        }

        // Sincronizar asociados (agregar sin eliminar los existentes)
        $lista->asociados()->syncWithoutDetaching($asociadosValidos);

        return $lista->fresh()->loadCount('asociados');
    }

    /**
     * Remover un asociado de una lista.
     *
     * @param int $listaId
     * @param int $asociadoId
     * @return Lista
     * @throws InvalidArgumentException
     */
    public function eliminarAsociado(int $listaId, int $asociadoId): Lista
    {
        $lista = Lista::find($listaId);

        if (!$lista) {
            throw new InvalidArgumentException('Lista no encontrada.', 404);
        }

        $lista->asociados()->detach($asociadoId);

        return $lista->fresh()->loadCount('asociados');
    }

    /**
     * Reemplazar todos los asociados de una lista.
     *
     * @param int $listaId
     * @param array<int> $asociadoIds
     * @return Lista
     * @throws InvalidArgumentException
     */
    public function reemplazarAsociados(int $listaId, array $asociadoIds): Lista
    {
        $lista = Lista::find($listaId);

        if (!$lista) {
            throw new InvalidArgumentException('Lista no encontrada.', 404);
        }

        $orgId = $this->obtenerOrganizacionId();

        // Validar que los asociados pertenezcan a la organización
        $asociadosValidos = Asociado::whereHas('organizaciones', function ($query) use ($orgId) {
            $query->where('organizacion_id', $orgId)
                ->where('asociado_organizacion.activo', true);
        })
            ->whereIn('id', $asociadoIds)
            ->pluck('id')
            ->toArray();

        if (count($asociadosValidos) !== count($asociadoIds)) {
            throw new InvalidArgumentException('Algunos asociados no pertenecen a esta organización.', 422);
        }

        // Reemplazar todos los asociados
        $lista->asociados()->sync($asociadosValidos);

        return $lista->fresh()->loadCount('asociados');
    }

    /**
     * Validar que el color sea hexadecimal válido.
     *
     * @param string $color
     * @return bool
     */
    private function validarColorHex(string $color): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
    }
}
