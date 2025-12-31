<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Facturacion extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla.
     *
     * @var string
     */
    protected $table = 'facturacion';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organizacion_id',
        'periodo',
        'cantidad_asociados',
        'monto',
        'fecha_pago',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_pago' => 'date',
        'cantidad_asociados' => 'integer',
        'monto' => 'decimal:2',
    ];

    /**
     * Obtener la organización a la que pertenece.
     *
     * @return BelongsTo
     */
    public function organizacion(): BelongsTo
    {
        return $this->belongsTo(Organizacion::class);
    }

    /**
     * Verificar si la facturación está pagada.
     *
     * @return bool
     */
    public function estaPagada(): bool
    {
        return $this->fecha_pago !== null;
    }

    /**
     * Verificar si la facturación está pendiente.
     *
     * @return bool
     */
    public function estaPendiente(): bool
    {
        return $this->fecha_pago === null;
    }

    /**
     * Calcular el monto total a pagar.
     *
     * @param float $precioUnitario
     * @return float
     */
    public function calcularMonto(float $precioUnitario = 2.00): float
    {
        return $this->cantidad_asociados * $precioUnitario;
    }

    /**
     * Marcar como pagada.
     *
     * @return void
     */
    public function marcarComoPagada(): void
    {
        $this->update(['fecha_pago' => now()]);
    }

    /**
     * Scope para obtener facturaciones pendientes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendientes($query)
    {
        return $query->whereNull('fecha_pago');
    }

    /**
     * Scope para obtener facturaciones pagadas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePagadas($query)
    {
        return $query->whereNotNull('fecha_pago');
    }

    /**
     * Scope para obtener por periodo específico.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $periodo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorPeriodo($query, string $periodo)
    {
        return $query->where('periodo', $periodo);
    }
}
