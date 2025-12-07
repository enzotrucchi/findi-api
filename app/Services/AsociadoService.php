<?php

namespace App\Services;

use App\DTOs\Asociado\AsociadoDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use App\DTOs\Asociado\FiltroAsociadoDTO;
use App\Models\Asociado;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
     */
    public function __construct() {}

    /**
     * Query base de asociados ligados a la organización seleccionada
     * del usuario autenticado.
     *
     * - Filtra por organizacion_seleccionada_id del usuario.
     * - Exige que la relación en el pivot esté activa (asociado_organizacion.activo = true).
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
     * Query de asociados de la organización seleccionada SIN filtrar por activo.
     * Útil para operaciones de activación/desactivación.
     */
    private function queryPorOrganizacionSinFiltroActivo(): Builder
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
            $q->where('organizacion_id', $orgId);
        });
    }

    /**
     * Obtener todos los asociados.
     * Utiliza Laravel Pagination internamente.
     * 
     *
     * @param bool $soloActivos
     * @param bool $soloAdmins
     * @return Collection<int, AsociadoDTO>
     */
    public function obtenerColeccion(FiltroAsociadoDTO $filtroDTO): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->queryPorOrganizacionSeleccionada();

        $perPage  = 10;
        $pageName = 'pagina';

        return $query
            ->orderBy('nombre', 'asc')
            ->paginate(perPage: $perPage, columns: ['*'], pageName: $pageName)
            // Si más adelante querés usar DTOs:
            // ->through(fn (Asociado $asociado) => AsociadoDTO::desdeModelo($asociado));
            ->through(fn($item) => ['asociado' => $item]);
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
        $activos = (clone $queryBase)->whereHas('organizaciones', function (Builder $q) {
            $q->where('organizacion_id', Auth::user()->organizacion_seleccionada_id)
                ->where('asociado_organizacion.activo', true);
        })->count();

        return [
            'asociados_totales' => $total,
            'asociados_activos' => $activos,
        ];
    }

    /**
     * Crear un nuevo asociado.
     *
     * @param AsociadoDTO $dto
     * @return Asociado
     * @throws InvalidArgumentException
     */
    public function crear(AsociadoDTO $dto): Asociado
    {
        $emailNormalizado = $this->normalizarEmail($dto->email);

        if ($this->existeEmail($emailNormalizado)) {
            throw new InvalidArgumentException('El email ya está registrado.');
        }

        $user = Auth::user();
        if (!$user instanceof Asociado) {
            abort(401, 'No autenticado.');
        }

        $orgId = $user->organizacion_seleccionada_id;
        if (!$orgId) {
            abort(403, 'No hay organización seleccionada.');
        }

        return DB::transaction(function () use ($dto, $emailNormalizado, $orgId) {
            // Crear el asociado
            $asociado = Asociado::create([
                'nombre' => $this->normalizarNombre($dto->nombre),
                'email' => $emailNormalizado,
                'telefono' => $dto->telefono ? $this->normalizarTelefono($dto->telefono) : null,
                'domicilio' => $dto->domicilio ? trim($dto->domicilio) : null,
            ]);

            // Vincular con la organización
            $asociado->organizaciones()->attach($orgId, [
                'activo' => true,
                'es_admin' => null,
                'fecha_alta' => now(),
            ]);

            return $asociado;
        });
    }

    /**
     * Actualizar un asociado existente.
     *
     * @param int $id
     * @param AsociadoDTO $dto
     * @return Asociado|null
     * @throws InvalidArgumentException
     */
    public function actualizar(int $id, AsociadoDTO $dto): ?Asociado
    {
        // Verificar que el asociado pertenezca a la organización seleccionada
        $asociado = $this->queryPorOrganizacionSeleccionada()
            ->where('asociados.id', $id)
            ->first();

        if (!$asociado) {
            return null;
        }

        // Validar email si cambió
        $emailNormalizado = $this->normalizarEmail($dto->email);
        if ($emailNormalizado !== $asociado->email && $this->existeEmail($emailNormalizado, $id)) {
            throw new InvalidArgumentException('El email ya está registrado.');
        }

        // Preparar datos normalizados
        $datosNormalizados = [
            'nombre' => $this->normalizarNombre($dto->nombre),
            'email' => $emailNormalizado,
            'telefono' => $dto->telefono ? $this->normalizarTelefono($dto->telefono) : null,
            'domicilio' => $dto->domicilio ? trim($dto->domicilio) : null,
        ];

        // Actualizar
        $asociado->update($datosNormalizados);

        return $asociado->fresh();
    }

    public function activar(int $id): ?Asociado
    {
        // Usar query sin filtro de activo para poder encontrar asociados inactivos
        $asociado = $this->queryPorOrganizacionSinFiltroActivo()
            ->where('asociados.id', $id)
            ->first();

        if (!$asociado) {
            return null;
        }

        $orgId = Auth::user()->organizacion_seleccionada_id;

        $asociado->organizaciones()->updateExistingPivot($orgId, [
            'activo' => true,
        ]);

        return $asociado->fresh();
    }

    public function desactivar(int $id): ?Asociado
    {
        // También usar query sin filtro para consistencia
        $asociado = $this->queryPorOrganizacionSinFiltroActivo()
            ->where('asociados.id', $id)
            ->first();

        if (!$asociado) {
            return null;
        }

        $orgId = Auth::user()->organizacion_seleccionada_id;

        $asociado->organizaciones()->updateExistingPivot($orgId, [
            'activo' => null,
        ]);

        return $asociado->fresh();
    }


    /**
     * Verificar si existe un email (excluyendo opcionalmente un ID).
     *
     * @param string $email
     * @param int|null $excluirId
     * @return bool
     */
    public function existeEmail(string $email, ?int $excluirId = null): bool
    {
        $query = Asociado::where('email', strtolower(trim($email)));
        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }

    /**
     * Normalizar email (lowercase y trim).
     *
     * @param string $email
     * @return string
     */
    private function normalizarEmail(string $email): string
    {
        return strtolower(trim($email));
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
