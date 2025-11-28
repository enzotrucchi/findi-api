<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movimiento extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fecha',
        'hora',
        'detalle',
        'monto',
        'tipo',
        'status',
        'adjunto',
        'proyecto_id',
        'asociado_id',
        'proveedor_id',
        'modo_pago_id',
        'organizacion_id',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'monto' => 'decimal:2',
    ];

    /**
     * Obtener el proyecto asociado.
     *
     * @return BelongsTo
     */
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    /**
     * Obtener el asociado.
     *
     * @return BelongsTo
     */
    public function asociado(): BelongsTo
    {
        return $this->belongsTo(Asociado::class);
    }

    /**
     * Obtener el proveedor.
     *
     * @return BelongsTo
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /**
     * Obtener el modo de pago.
     *
     * @return BelongsTo
     */
    public function modoPago(): BelongsTo
    {
        return $this->belongsTo(ModoPago::class);
    }

    /**
     * Obtener la organización.
     *
     * @return BelongsTo
     */
    public function organizacion(): BelongsTo
    {
        return $this->belongsTo(Organizacion::class);
    }
}
