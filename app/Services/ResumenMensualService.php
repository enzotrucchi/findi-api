<?php

namespace App\Services;

use App\Models\Organizacion;
use App\Models\Movimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para generar resumen mensual de la organización.
 */
class ResumenMensualService
{
    /**
     * Obtener totalizadores del mes para una organización.
     *
     * @param Organizacion $organizacion
     * @param string|null $periodo Formato YYYY-MM (por defecto mes anterior)
     * @return array
     */
    public function obtenerTotalizadores(Organizacion $organizacion, ?string $periodo = null): array
    {
        if (!$periodo) {
            $periodo = now()->subMonth()->format('Y-m');
        }

        // Parsear período
        $fecha = Carbon::createFromFormat('Y-m', $periodo);
        $inicioMes = $fecha->copy()->startOfMonth();
        $finMes = $fecha->copy()->endOfMonth();
        $periodoVisual = $fecha->format('m/Y');

        // Total de asociados activos en la organización
        $asociadosActivos = $organizacion->asociados()
            ->wherePivot('activo', true)
            ->count();

        // Total de proyectos y proyectos activos
        $totalProyectos = $organizacion->proyectos()->count();
        $proyectosActivos = $organizacion->proyectos()
            ->whereNull('fecha_realizacion')
            ->count();

        // Movimientos del mes
        $movimientos = Movimiento::where('organizacion_id', $organizacion->id)
            ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->get();

        // Calcular ingresos, egresos y balance
        $ingresos = $movimientos->where('tipo', 'ingreso')->sum('monto');
        $egresos = $movimientos->where('tipo', 'egreso')->sum('monto');
        $balance = $ingresos - $egresos;

        // Cantidad de movimientos
        $cantidadMovimientos = $movimientos->count();

        return [
            'periodo' => $periodo,
            'periodo_visual' => $periodoVisual,
            'asociados_activos' => $asociadosActivos,
            'total_proyectos' => $totalProyectos,
            'proyectos_activos' => $proyectosActivos,
            'ingresos' => (float) $ingresos,
            'egresos' => (float) $egresos,
            'balance' => (float) $balance,
            'cantidad_movimientos' => $cantidadMovimientos,
        ];
    }

    /**
     * Obtener organizaciones que deben recibir el resumen mensual.
     * Solo organizaciones habilitadas y de producción.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerOrganizacionesParaResumen()
    {
        return Organizacion::where('habilitada', true)
            ->where('es_prueba', false)
            ->get();
    }
}
