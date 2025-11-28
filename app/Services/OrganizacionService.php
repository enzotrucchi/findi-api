<?php

namespace App\Services;

use App\DTOs\Organizacion\ActualizarOrganizacionDTO;
use App\DTOs\Organizacion\CrearOrganizacionDTO;
use App\DTOs\Organizacion\OrganizacionDTO;
use App\Repositories\Contracts\OrganizacionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Servicio de Organizaciones
 * 
 * Contiene toda la lógica de negocio relacionada con organizaciones.
 */
class OrganizacionService
{
    /**
     * Constructor.
     *
     * @param OrganizacionRepositoryInterface $organizacionRepository
     */
    public function __construct(
        private OrganizacionRepositoryInterface $organizacionRepository
    ) {}

    /**
     * Obtener todas las organizaciones.
     *
     * @param bool $soloPrueba
     * @param bool $soloProduccion
     * @return Collection<int, OrganizacionDTO>
     */
    public function obtenerTodos(bool $soloPrueba = false, bool $soloProduccion = false): Collection
    {
        if ($soloPrueba) {
            $organizaciones = $this->organizacionRepository->obtenerPrueba();
        } elseif ($soloProduccion) {
            $organizaciones = $this->organizacionRepository->obtenerProduccion();
        } else {
            $organizaciones = $this->organizacionRepository->obtenerTodos();
        }

        return $organizaciones->map(fn($organizacion) => OrganizacionDTO::desdeModelo($organizacion));
    }

    /**
     * Obtener una organización por ID.
     *
     * @param int $id
     * @return OrganizacionDTO|null
     */
    public function obtenerPorId(int $id): ?OrganizacionDTO
    {
        $organizacion = $this->organizacionRepository->obtenerPorId($id);

        if (!$organizacion) {
            return null;
        }

        return OrganizacionDTO::desdeModelo($organizacion);
    }

    /**
     * Crear una nueva organización.
     *
     * @param CrearOrganizacionDTO $dto
     * @return OrganizacionDTO
     * @throws InvalidArgumentException
     */
    public function crear(CrearOrganizacionDTO $dto): OrganizacionDTO
    {
        // Validar que el nombre no exista
        if ($this->organizacionRepository->existeNombre($dto->nombre)) {
            throw new InvalidArgumentException('El nombre de organización ya está registrado.');
        }

        // Validar fecha de fin de prueba
        if ($dto->esPrueba && $dto->fechaFinPrueba === null) {
            throw new InvalidArgumentException('Las organizaciones de prueba deben tener una fecha de fin de prueba.');
        }

        // Normalizar datos
        $datosNormalizados = [
            'nombre' => trim($dto->nombre),
            'admin_email' => strtolower(trim($dto->adminEmail)),
            'admin_nombre' => $this->normalizarNombre($dto->adminNombre),
            'fecha_alta' => $dto->fechaAlta,
            'es_prueba' => $dto->esPrueba,
            'fecha_fin_prueba' => $dto->fechaFinPrueba,
        ];

        $organizacion = DB::transaction(function () use ($datosNormalizados) {
            return $this->organizacionRepository->crear($datosNormalizados);
        });

        return OrganizacionDTO::desdeModelo($organizacion);
    }

    /**
     * Actualizar una organización existente.
     *
     * @param int $id
     * @param ActualizarOrganizacionDTO $dto
     * @return OrganizacionDTO|null
     * @throws InvalidArgumentException
     */
    public function actualizar(int $id, ActualizarOrganizacionDTO $dto): ?OrganizacionDTO
    {
        $organizacion = $this->organizacionRepository->obtenerPorId($id);

        if (!$organizacion) {
            return null;
        }

        // Validar nombre si se está actualizando
        if ($dto->nombre !== null && $this->organizacionRepository->existeNombre($dto->nombre, $id)) {
            throw new InvalidArgumentException('El nombre de organización ya está registrado.');
        }

        // Normalizar datos
        $datosNormalizados = [];
        
        if ($dto->nombre !== null) {
            $datosNormalizados['nombre'] = trim($dto->nombre);
        }
        
        if ($dto->adminEmail !== null) {
            $datosNormalizados['admin_email'] = strtolower(trim($dto->adminEmail));
        }
        
        if ($dto->adminNombre !== null) {
            $datosNormalizados['admin_nombre'] = $this->normalizarNombre($dto->adminNombre);
        }
        
        if ($dto->fechaAlta !== null) {
            $datosNormalizados['fecha_alta'] = $dto->fechaAlta;
        }
        
        if ($dto->esPrueba !== null) {
            $datosNormalizados['es_prueba'] = $dto->esPrueba;
        }
        
        if ($dto->fechaFinPrueba !== null) {
            $datosNormalizados['fecha_fin_prueba'] = $dto->fechaFinPrueba;
        }

        DB::transaction(function () use ($id, $datosNormalizados) {
            $this->organizacionRepository->actualizar($id, $datosNormalizados);
        });

        // Refrescar el modelo
        $organizacionActualizada = $this->organizacionRepository->obtenerPorId($id);

        return OrganizacionDTO::desdeModelo($organizacionActualizada);
    }

    /**
     * Eliminar una organización.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->organizacionRepository->eliminar($id);
        });
    }

    /**
     * Buscar organizaciones por término.
     *
     * @param string $termino
     * @return Collection<int, OrganizacionDTO>
     */
    public function buscar(string $termino): Collection
    {
        $organizaciones = $this->organizacionRepository->buscar($termino);
        return $organizaciones->map(fn($organizacion) => OrganizacionDTO::desdeModelo($organizacion));
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
