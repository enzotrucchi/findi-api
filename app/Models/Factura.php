<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Factura extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla.
     *
     * @var string
     */
    protected $table = 'facturas';

    /**
     * Estados posibles de la factura.
     */
    const ESTADO_PENDING = 'pending';
    const ESTADO_PAID = 'paid';
    const ESTADO_EXPIRED = 'expired';
    const ESTADO_CANCELLED = 'cancelled';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organizacion_id',
        'periodo',
        'fecha_corte',
        'cantidad_asociados',
        'precio_unitario',
        'monto_total',
        'fecha_vencimiento',
        'estado',
        'fecha_pago',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_corte' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'datetime',
        'precio_unitario' => 'decimal:2',
        'monto_total' => 'decimal:2',
        'cantidad_asociados' => 'integer',
    ];

    /**
     * Obtener la organización a la que pertenece la factura.
     *
     * @return BelongsTo
     */
    public function organizacion(): BelongsTo
    {
        return $this->belongsTo(Organizacion::class);
    }

    /**
     * Verificar si la factura está pagada.
     *
     * @return bool
     */
    public function estaPagada(): bool
    {
        return $this->estado === self::ESTADO_PAID;
    }

    /**
     * Verificar si la factura está vencida.
     *
     * @return bool
     */
    public function estaVencida(): bool
    {
        return $this->estado === self::ESTADO_EXPIRED ||
            ($this->estado === self::ESTADO_PENDING && now()->isAfter($this->fecha_vencimiento));
    }

    /**
     * Verificar si la factura está pendiente.
     *
     * @return bool
     */
    public function estaPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDING;
    }

    /**
     * Marcar la factura como pagada.
     *
     * @return void
     */
    public function marcarComoPagada(): void
    {
        $this->update([
            'estado' => self::ESTADO_PAID,
            'fecha_pago' => now(),
        ]);
    }

    /**
     * Marcar la factura como vencida.
     *
     * @return void
     */
    public function marcarComoVencida(): void
    {
        $this->update([
            'estado' => self::ESTADO_EXPIRED,
        ]);
    }

    /**
     * Cancelar la factura.
     *
     * @return void
     */
    public function cancelar(): void
    {
        $this->update([
            'estado' => self::ESTADO_CANCELLED,
        ]);
    }

    /**
     * Scope para obtener facturas pendientes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDING);
    }

    /**
     * Scope para obtener facturas pagadas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePagadas($query)
    {
        return $query->where('estado', self::ESTADO_PAID);
    }

    /**
     * Scope para obtener facturas vencidas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVencidas($query)
    {
        return $query->where(function ($q) {
            $q->where('estado', self::ESTADO_EXPIRED)
                ->orWhere(function ($q2) {
                    $q2->where('estado', self::ESTADO_PENDING)
                        ->where('fecha_vencimiento', '<', now());
                });
        });
    }

    /**
     * Scope para obtener facturas de un periodo específico.
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
