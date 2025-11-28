<?php

namespace App\Repositories;

use App\Models\Asociado;
use App\Repositories\Contracts\AsociadoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Implementación del repositorio de Asociados
 * 
 * Maneja todas las operaciones de acceso a datos de asociados.
 * No contiene lógica de negocio.
 */
class AsociadoRepository implements AsociadoRepositoryInterface
{
    /**
     * Obtener todos los asociados.
     *
     * @return Collection<int, Asociado>
     */
    public function obtenerTodos(): Collection
    {
        return Asociado::orderBy('nombre')->get();
    }

    /**
     * Obtener asociados activos.
     *
     * @return Collection<int, Asociado>
     */
    public function obtenerActivos(): Collection
    {
        return Asociado::where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener asociados administradores.
     *
     * @return Collection<int, Asociado>
     */
    public function obtenerAdministradores(): Collection
    {
        return Asociado::where('es_admin', true)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener un asociado por ID.
     *
     * @param int $id
     * @return Asociado|null
     */
    public function obtenerPorId(int $id): ?Asociado
    {
        return Asociado::find($id);
    }

    /**
     * Crear un nuevo asociado.
     *
     * @param array<string, mixed> $datos
     * @return Asociado
     */
    public function crear(array $datos): Asociado
    {
        return Asociado::create($datos);
    }

    /**
     * Actualizar un asociado existente.
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool
    {
        $asociado = $this->obtenerPorId($id);
        
        if (!$asociado) {
            return false;
        }

        return $asociado->update($datos);
    }

    /**
     * Eliminar un asociado.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        $asociado = $this->obtenerPorId($id);
        
        if (!$asociado) {
            return false;
        }

        return $asociado->delete();
    }

    /**
     * Buscar asociados por término.
     *
     * @param string $termino
     * @return Collection<int, Asociado>
     */
    public function buscar(string $termino): Collection
    {
        return Asociado::where('nombre', 'like', "%{$termino}%")
            ->orWhere('email', 'like', "%{$termino}%")
            ->orWhere('telefono', 'like', "%{$termino}%")
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Verificar si un email ya existe.
     *
     * @param string $email
     * @param int|null $excluirId ID a excluir de la búsqueda (para actualizaciones)
     * @return bool
     */
    public function existeEmail(string $email, ?int $excluirId = null): bool
    {
        $query = Asociado::where('email', $email);

        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }
}
