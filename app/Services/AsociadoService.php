<?php

namespace App\Services;

use App\DTOs\Asociado\ActualizarAsociadoDTO;
use App\DTOs\Asociado\AsociadoDTO;
use App\DTOs\Asociado\CrearAsociadoDTO;
use App\Repositories\Contracts\AsociadoRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Servicio de Asociados
 * 
 * Contiene toda la lógica de negocio relacionada con asociados.
 */
class AsociadoService
{
    /**
     * Constructor.
     *
     * @param AsociadoRepositoryInterface $asociadoRepository
     */
    public function __construct(
        private AsociadoRepositoryInterface $asociadoRepository
    ) {}

    /**
     * Obtener todos los asociados.
     *
     * @param bool $soloActivos
     * @param bool $soloAdmins
     * @return Collection<int, AsociadoDTO>
     */
    public function obtenerColeccion(bool $soloActivos = false, bool $soloAdmins = false): Collection
    {
        if ($soloAdmins) {
            $asociados = $this->asociadoRepository->obtenerAdministradores();
        } elseif ($soloActivos) {
            $asociados = $this->asociadoRepository->obtenerActivos();
        } else {
            $asociados = $this->asociadoRepository->obtenerColeccion();
        }

        return $asociados->map(fn($asociado) => AsociadoDTO::desdeModelo($asociado));
    }

    /**
     * Obtener un asociado por ID.
     *
     * @param int $id
     * @return AsociadoDTO|null
     */
    public function obtenerPorId(int $id): ?AsociadoDTO
    {
        $asociado = $this->asociadoRepository->obtenerPorId($id);

        if (!$asociado) {
            return null;
        }

        return AsociadoDTO::desdeModelo($asociado);
    }

    /**
     * Crear un nuevo asociado.
     *
     * @param CrearAsociadoDTO $dto
     * @return AsociadoDTO
     * @throws InvalidArgumentException
     */
    public function crear(CrearAsociadoDTO $dto): AsociadoDTO
    {
        // Validar que el email no exista
        if ($this->asociadoRepository->existeEmail($dto->email)) {
            throw new InvalidArgumentException('El email ya está registrado.');
        }

        // Normalizar nombre (capitalizar palabras)
        $datosNormalizados = [
            'nombre' => $this->normalizarNombre($dto->nombre),
            'email' => strtolower(trim($dto->email)),
            'telefono' => $this->normalizarTelefono($dto->telefono),
            'domicilio' => $dto->domicilio ? trim($dto->domicilio) : null,
            'es_admin' => $dto->esAdmin,
            'activo' => $dto->activo,
        ];

        $asociado = DB::transaction(function () use ($datosNormalizados) {
            return $this->asociadoRepository->crear($datosNormalizados);
        });

        return AsociadoDTO::desdeModelo($asociado);
    }

    /**
     * Actualizar un asociado existente.
     *
     * @param int $id
     * @param ActualizarAsociadoDTO $dto
     * @return AsociadoDTO|null
     * @throws InvalidArgumentException
     */
    public function actualizar(int $id, ActualizarAsociadoDTO $dto): ?AsociadoDTO
    {
        $asociado = $this->asociadoRepository->obtenerPorId($id);

        if (!$asociado) {
            return null;
        }

        // Validar email si se está actualizando
        if ($dto->email !== null && $this->asociadoRepository->existeEmail($dto->email, $id)) {
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

        if ($dto->domicilio !== null) {
            $datosNormalizados['domicilio'] = trim($dto->domicilio);
        }

        if ($dto->esAdmin !== null) {
            $datosNormalizados['es_admin'] = $dto->esAdmin;
        }

        if ($dto->activo !== null) {
            $datosNormalizados['activo'] = $dto->activo;
        }

        DB::transaction(function () use ($id, $datosNormalizados) {
            $this->asociadoRepository->actualizar($id, $datosNormalizados);
        });

        // Refrescar el modelo
        $asociadoActualizado = $this->asociadoRepository->obtenerPorId($id);

        return AsociadoDTO::desdeModelo($asociadoActualizado);
    }

    /**
     * Eliminar un asociado.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->asociadoRepository->eliminar($id);
        });
    }

    /**
     * Buscar asociados por término.
     *
     * @param string $termino
     * @return Collection<int, AsociadoDTO>
     */
    public function buscar(string $termino): Collection
    {
        $asociados = $this->asociadoRepository->buscar($termino);
        return $asociados->map(fn($asociado) => AsociadoDTO::desdeModelo($asociado));
    }

    /**
     * Obtener asociados por múltiples IDs.
     *
     * @param array<int> $ids
     * @return Collection<int, AsociadoDTO>
     */
    public function obtenerPorIds(array $ids): Collection
    {
        $asociados = $this->asociadoRepository->obtenerPorIds($ids);
        return $asociados->map(fn($asociado) => AsociadoDTO::desdeModelo($asociado));
    }

    /**
     * Verificar si existe un asociado.
     *
     * @param int $id
     * @return bool
     */
    public function existePorId(int $id): bool
    {
        return $this->asociadoRepository->existePorId($id);
    }

    /**
     * Contar asociados.
     *
     * @param bool $soloActivos
     * @return int
     */
    public function contar(bool $soloActivos = false): int
    {
        return $this->asociadoRepository->contarColeccion($soloActivos);
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
     * Normalizar teléfono (eliminar espacios y caracteres no numéricos excepto +, - y paréntesis).
     *
     * @param string $telefono
     * @return string
     */
    private function normalizarTelefono(string $telefono): string
    {
        return preg_replace('/[^\d\+\-\(\)\s]/', '', trim($telefono));
    }
}
