<?php

namespace App\Services;

use App\DTOs\PlanPago\CuotaDTO;
use App\DTOs\PlanPago\FiltroPlanPagoDTO;
use App\DTOs\PlanPago\PlanPagoDTO;
use App\Models\Asociado;
use App\Models\Cuota;
use App\Models\Movimiento;
use App\Models\PlanPago;
use App\Services\Traits\ObtenerOrganizacionSeleccionada;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PlanPagoService
{
    use ObtenerOrganizacionSeleccionada;

    public function __construct() {}

    public function crearPlanPago(PlanPagoDTO $dto): PlanPago|EloquentCollection
    {
        $orgId = $this->obtenerOrganizacionId();
        $asociadoIds = array_values(array_unique($dto->asociadoIds));

        if ($asociadoIds === []) {
            throw new InvalidArgumentException('Debes enviar al menos un asociado.', 400);
        }

        $asociadosValidos = Asociado::query()
            ->whereIn('id', $asociadoIds)
            ->whereHas('organizaciones', function ($query) use ($orgId): void {
                $query->where('organizaciones.id', $orgId)
                    ->where('asociado_organizacion.activo', true);
            })
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $asociadosInvalidos = array_values(array_diff($asociadoIds, $asociadosValidos));
        if ($asociadosInvalidos !== []) {
            throw new InvalidArgumentException(
                'Uno o más asociados no pertenecen a la organización seleccionada: '
                . implode(', ', $asociadosInvalidos),
                400
            );
        }

        if (is_null($dto->importeTotal) && is_null($dto->importePorCuota)) {
            throw new InvalidArgumentException('Debes enviar importe_total o importe_por_cuota.', 400);
        }

        return DB::transaction(function () use ($dto, $orgId, $asociadoIds): PlanPago|EloquentCollection {
            if (!is_null($dto->importeTotal)) {
                $importeTotal = round($dto->importeTotal, 2);
            } elseif ($dto->cuotas !== []) {
                $importeTotal = round((float) collect($dto->cuotas)
                    ->sum(fn (CuotaDTO $cuota): float => $cuota->importe), 2);
            } else {
                $importeTotal = round((float) $dto->importePorCuota * $dto->cantidadCuotas, 2);
            }

            $cuotasPlantilla = $this->resolverCuotas($dto, (float) $importeTotal);
            $planes = new EloquentCollection();

            foreach ($asociadoIds as $asociadoId) {
                $planPago = PlanPago::create([
                    'organizacion_id' => $orgId,
                    'asociado_id' => $asociadoId,
                    'descripcion' => $dto->descripcion,
                    'total' => $importeTotal,
                    'estado' => 'activo',
                ]);

                foreach ($cuotasPlantilla as $cuotaDTO) {
                    $planPago->cuotas()->create($cuotaDTO->aArray());
                }

                $planes->push($planPago->load(['asociado', 'cuotas']));
            }

            return $planes->count() === 1 ? $planes->first() : $planes;
        });
    }

    /**
     * @return array<int, CuotaDTO>
     */
    private function resolverCuotas(PlanPagoDTO $dto, float $importeTotal): array
    {
        if ($dto->cuotas !== []) {
            $cuotas = collect($dto->cuotas)
                ->sortBy(static fn (CuotaDTO $cuota): int => $cuota->numero)
                ->values()
                ->all();

            if (count($cuotas) !== $dto->cantidadCuotas) {
                throw new InvalidArgumentException('La cantidad de cuotas enviadas no coincide con cantidad_cuotas.', 400);
            }

            $sumaCuotas = round((float) collect($cuotas)
                ->sum(fn (CuotaDTO $cuota): float => $cuota->importe), 2);

            if ($sumaCuotas !== round($importeTotal, 2)) {
                throw new InvalidArgumentException('La suma de cuotas no coincide con el importe total del plan.', 400);
            }

            return $cuotas;
        }

        $importeBase = $dto->importePorCuota ?? round($importeTotal / $dto->cantidadCuotas, 2);
        $fechaBase = Carbon::parse($dto->fechaPrimerVencimiento)->startOfDay();
        $importeAcumulado = 0.0;
        $cuotas = [];

        for ($numero = 1; $numero <= $dto->cantidadCuotas; $numero++) {
            $esUltimaCuota = $numero === $dto->cantidadCuotas;
            $importeCuota = $esUltimaCuota
                ? round($importeTotal - $importeAcumulado, 2)
                : round($importeBase, 2);

            $importeAcumulado += $importeCuota;

            $cuotas[] = new CuotaDTO(
                numero: $numero,
                importe: $importeCuota,
                fechaVencimiento: $fechaBase
                    ->copy()
                    ->addMonths(($numero - 1) * $dto->frecuenciaMensual)
                    ->toDateString(),
            );
        }

        return $cuotas;
    }

    public function obtenerPorId(int $id): ?PlanPago
    {
        return PlanPago::with(['asociado', 'cuotas.movimiento'])->find($id);
    }

    public function obtenerColeccion(?FiltroPlanPagoDTO $filtroDTO = null): EloquentCollection
    {
        $query = PlanPago::query()
            ->with(['asociado', 'cuotas'])
            ->latest('id');

        if ($filtroDTO instanceof FiltroPlanPagoDTO) {
            $this->aplicarFiltrosColeccion($query, $filtroDTO);
        }

        return $query->get();
    }

    private function aplicarFiltrosColeccion($query, FiltroPlanPagoDTO $filtroDTO): void
    {
        if ($filtroDTO->getAsociadoId()) {
            $query->where('asociado_id', $filtroDTO->getAsociadoId());
        }

        if ($filtroDTO->getEstado()) {
            $query->where('estado', $filtroDTO->getEstado());
        }

        if ($filtroDTO->getDescripcion()) {
            $query->where('descripcion', 'like', '%' . $filtroDTO->getDescripcion() . '%');
        }

        if ($filtroDTO->getFechaDesde()) {
            $query->whereDate('created_at', '>=', $filtroDTO->getFechaDesde());
        }

        if ($filtroDTO->getFechaHasta()) {
            $query->whereDate('created_at', '<=', $filtroDTO->getFechaHasta());
        }
    }

    public function cancelarCuota(int $cuotaId): Cuota
    {
        $orgId = $this->obtenerOrganizacionId();

        $cuota = Cuota::query()
            ->whereHas('planPago', function ($query) use ($orgId): void {
                $query->where('organizacion_id', $orgId);
            })
            ->with('planPago')
            ->find($cuotaId);

        if (!$cuota) {
            throw new InvalidArgumentException('Cuota no encontrada.', 404);
        }

        if (!in_array($cuota->estado, ['pendiente', 'vencida'], true)) {
            throw new InvalidArgumentException('Solo se pueden cancelar cuotas pendientes o vencidas.', 400);
        }

        return DB::transaction(function () use ($cuota): Cuota {
            $planPago = $cuota->planPago;
            $totalCuotasPlan = $planPago->cuotas()->count();
            $descripcionMovimiento = "Pago cuota {$cuota->numero}/{$totalCuotasPlan} - Plan {$planPago->descripcion}";

            $movimiento = Movimiento::create([
                'fecha' => now()->toDateString(),
                'hora' => now()->format('H:i:s'),
                'detalle' => $descripcionMovimiento,
                'monto' => $cuota->importe,
                'tipo' => 'ingreso',
                'status' => 'aprobado',
                'asociado_id' => $planPago->asociado_id,
                'organizacion_id' => $planPago->organizacion_id,
                'referencia_tipo' => 'cuota',
                'referencia_id' => $cuota->id,
            ]);

            $cuota->update([
                'estado' => 'pagada',
                'fecha_pago' => now()->toDateString(),
                'movimiento_id' => $movimiento->id,
            ]);

            $tieneCuotasPendientes = $planPago->cuotas()
                ->whereIn('estado', ['pendiente', 'vencida'])
                ->exists();

            if (!$tieneCuotasPendientes && $planPago->estado === 'activo') {
                $planPago->update(['estado' => 'finalizado']);
            }

            return $cuota->fresh(['planPago', 'movimiento']);
        });
    }
}
