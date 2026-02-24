<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuota extends Model
{
    use HasFactory;

    protected $table = 'cuotas';

    protected $fillable = [
        'plan_pago_id',
        'numero',
        'importe',
        'fecha_vencimiento',
        'estado',
        'fecha_pago',
        'movimiento_id',
        'recordatorio_enviado_at',
    ];

    protected $casts = [
        'importe' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
        'recordatorio_enviado_at' => 'datetime',
    ];

    public function planPago(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_pago_id');
    }

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class);
    }
}
