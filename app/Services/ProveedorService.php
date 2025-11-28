<?php

namespace App\Services;

use App\DTOs\Proveedor\ActualizarProveedorDTO;
use App\DTOs\Proveedor\CrearProveedorDTO;
use App\DTOs\Proveedor\ProveedorDTO;
use App\Repositories\Contracts\ProveedorRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Servicio de Proveedores
 * 
 * Contiene toda la lógica de negocio relacionada con proveedores.
 */
class ProveedorService
{
    /**
     * Constructor.
     *
     * @param ProveedorRepositoryInterface $proveedorRepository
     */
    public function __construct(
        private ProveedorRepositoryInterface $proveedorRepository
    ) {}

    /**
     * Obtener todos los proveedores.
     *
     * @param bool $soloActivos
     * @return Collection<int, ProveedorDTO>
     */
    public function obtenerColeccion(bool $soloActivos = false): Collection
    {
        $proveedores = $soloActivos
            ? $this->proveedorRepository->obtenerActivos()
            : $this->proveedorRepository->obtenerColeccion();

        return $proveedores->map(fn($proveedor) => ProveedorDTO::desdeModelo($proveedor));
    }

    /**
     * Obtener un proveedor por ID.
     *
     * @param int $id
     * @return ProveedorDTO|null
     */
    public function obtenerPorId(int $id): ?ProveedorDTO
    {
        $proveedor = $this->proveedorRepository->obtenerPorId($id);

        if (!$proveedor) {
            return null;
        }

        return ProveedorDTO::desdeModelo($proveedor);
    }

    /**
     * Crear un nuevo proveedor.
     *
     * @param CrearProveedorDTO $dto
     * @return ProveedorDTO
     * @throws InvalidArgumentException
     */
    public function crear(CrearProveedorDTO $dto): ProveedorDTO
    {
        // Validar que el email no exista
        if ($this->proveedorRepository->existeEmail($dto->email)) {
            throw new InvalidArgumentException('El email ya está registrado.');
        }

        // Normalizar nombre (capitalizar palabras)
        $datosNormalizados = [
            'nombre' => $this->normalizarNombre($dto->nombre),
            'email' => strtolower(trim($dto->email)),
            'telefono' => $this->normalizarTelefono($dto->telefono),
            'activo' => $dto->activo,
        ];

        $proveedor = DB::transaction(function () use ($datosNormalizados) {
            return $this->proveedorRepository->crear($datosNormalizados);
        });

        return ProveedorDTO::desdeModelo($proveedor);
    }

    /**
     * Actualizar un proveedor existente.
     *
     * @param int $id
     * @param ActualizarProveedorDTO $dto
     * @return ProveedorDTO|null
     * @throws InvalidArgumentException
     */
    public function actualizar(int $id, ActualizarProveedorDTO $dto): ?ProveedorDTO
    {
        $proveedor = $this->proveedorRepository->obtenerPorId($id);

        if (!$proveedor) {
            return null;
        }

        // Validar email si se está actualizando
        if ($dto->email !== null && $this->proveedorRepository->existeEmail($dto->email, $id)) {
            throw new InvalidArgumentException('El email ya está registrado.');
        }

        // Normalizar datos
        $datosNormalizados = [];

        if ($dto->nombre !== null) {
            $datosNormalizados['nombre'] = $this->normalizarNombre($dto->nombre);
        }

        if ($dto->email !== null) {
            $datosNormalizados['email'] = strtolower(trim($dto->email));
        }

        if ($dto->telefono !== null) {
            $datosNormalizados['telefono'] = $this->normalizarTelefono($dto->telefono);
        }

        if ($dto->activo !== null) {
            $datosNormalizados['activo'] = $dto->activo;
        }

        DB::transaction(function () use ($id, $datosNormalizados) {
            $this->proveedorRepository->actualizar($id, $datosNormalizados);
        });

        // Refrescar el modelo
        $proveedorActualizado = $this->proveedorRepository->obtenerPorId($id);

        return ProveedorDTO::desdeModelo($proveedorActualizado);
    }

    /**
     * Eliminar un proveedor.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->proveedorRepository->eliminar($id);
        });
    }

    /**
     * Buscar proveedores por término.
     *
     * @param string $termino
     * @return Collection<int, ProveedorDTO>
     */
    public function buscar(string $termino): Collection
    {
        $proveedores = $this->proveedorRepository->buscar($termino);

        return $proveedores->map(fn($proveedor) => ProveedorDTO::desdeModelo($proveedor));
    }

    /**
     * Obtener proveedores por múltiples IDs.
     *
     * @param array<int> $ids
     * @return Collection<int, ProveedorDTO>
     */
    public function obtenerPorIds(array $ids): Collection
    {
        $proveedores = $this->proveedorRepository->obtenerPorIds($ids);
        return $proveedores->map(fn($proveedor) => ProveedorDTO::desdeModelo($proveedor));
    }

    /**
     * Verificar si existe un proveedor.
     *
     * @param int $id
     * @return bool
     */
    public function existePorId(int $id): bool
    {
        return $this->proveedorRepository->existePorId($id);
    }

    /**
     * Contar proveedores.
     *
     * @param bool $soloActivos
     * @return int
     */
    public function contar(bool $soloActivos = false): int
    {
        return $this->proveedorRepository->contarColeccion($soloActivos);
    }

    /**
     * Normalizar nombre (capitalizar cada palabra).
     *
     * @param string $nombre
     * @return string
     */
    private function normalizarNombre(string $nombre): string
    {
        return mb_convert_case(trim($nombre), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Normalizar teléfono (eliminar caracteres no numéricos excepto + y espacios).
     *
     * @param string $telefono
     * @return string
     */
    private function normalizarTelefono(string $telefono): string
    {
        return trim($telefono);
    }
}
