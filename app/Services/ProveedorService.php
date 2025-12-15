<?php

namespace App\Services;

use App\DTOs\Proveedor\ProveedorDTO;
use App\DTOs\Proveedor\FiltroProveedorDTO;
use App\Services\Traits\ObtenerOrganizacionSeleccionada;
use App\Models\Proveedor;
use App\Models\Asociado;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

/**
 * Servicio de Proveedores
 * 
 * Contiene toda la lógica de negocio relacionada con proveedores.
 */
class ProveedorService
{
    use ObtenerOrganizacionSeleccionada;

    /**
     * Constructor.
     *
     */
    public function __construct() {}

    /**
     * Obtener todos los proveedores.
     *
     * @param bool $soloActivos
     * @return Collection<int, ProveedorDTO>
     */
    public function obtenerColeccion(FiltroProveedorDTO $filtroDTO): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Proveedor::query();

        $perPage  = 10;

        return $query
            ->orderBy('nombre', 'asc')
            ->paginate(perPage: $perPage, columns: ['*'], pageName: 'pagina', page: $filtroDTO->getPagina());
    }

    /**
     * Obtener movimientos de un asociado por su ID.
     *
     * @param int $id
     * @return Collection<int, mixed>|null
     */
    public function obtenerMovimientos(int $id): ?Collection
    {
        // Verificar que el asociado pertenezca a la organización seleccionada
        $proveedor = Proveedor::query()
            ->where('id', $id)
            ->first();

        if (!$proveedor) {
            return null;
        }

        return $proveedor->movimientos()->with('modoPago')->orderBy('fecha', 'desc')->get();
    }

    /**
     * Validar si existe un email para un proveedor en la organización.
     *
     * @param string $email
     * @param int|null $exceptoId
     * @return bool
     */
    private function existeEmail(string $email, ?int $exceptoId = null): bool
    {
        $query = Proveedor::query()
            ->where('email', strtolower(trim($email)));

        if ($exceptoId) {
            $query->where('id', '!=', $exceptoId);
        }

        return $query->exists();
    }

    /**
     * Crear un nuevo proveedor.
     *
     * @param array<string, mixed> $datos
     * @return ProveedorDTO
     * @throws InvalidArgumentException
     */
    public function crear(ProveedorDTO $dto): Proveedor
    {
        $emailNormalizado = strtolower(trim($dto->email));

        // Validar que el email no exista en esta organización
        if ($this->existeEmail($emailNormalizado)) {
            throw new InvalidArgumentException('El email ya está registrado en esta organización.');
        }

        $orgId = $this->obtenerOrganizacionId();

        $proveedor = DB::transaction(function () use ($dto, $emailNormalizado, $orgId) {
            return Proveedor::create([
                'organizacion_id' => $orgId,
                'nombre' => $this->normalizarNombre($dto->nombre),
                'email' => $emailNormalizado,
                'telefono' => $this->normalizarTelefono($dto->telefono),
                'activo' => $dto->activo ?? true,
            ]);
        });

        return $proveedor;
    }

    /**
     * Actualizar un proveedor existente.
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return ProveedorDTO|null
     * @throws InvalidArgumentException
     */
    public function actualizar(int $id, ProveedorDTO $dto): ?Proveedor
    {
        $query = Proveedor::query();

        $proveedor = $query->where('id', $id)
            ->first();

        if (!$proveedor) {
            return null;
        }

        // Validar email si se está actualizando
        if ($dto->email !== null && $this->existeEmail($dto->email, $id)) {
            throw new InvalidArgumentException('El email ya está registrado en esta organización.');
        }

        /**
         * Si el proveedor no corresponde a la organización seleccionada,
         * no permitir la actualización.
         */
        $organizacionIdSeleccionada = $this->obtenerOrganizacionId();
        if ($proveedor->organizacion_id !== $organizacionIdSeleccionada) {
            throw new InvalidArgumentException('El proveedor no pertenece a la organización seleccionada.');
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

        DB::transaction(function () use ($proveedor, $datosNormalizados) {
            $proveedor->update($datosNormalizados);
        });

        // Refrescar el modelo
        return $proveedor->fresh();
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
            $query = Proveedor::query();

            $proveedor = $query->where('id', $id)
                ->first();

            if (!$proveedor) {
                return false;
            }

            return $proveedor->delete();
        });
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
     * Normalizar teléfono.
     *
     * @param string $telefono
     * @return string
     */
    private function normalizarTelefono(string $telefono): string
    {
        return trim($telefono);
    }
}
