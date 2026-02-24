<?php

namespace App\Models;

use App\Models\Traits\TieneOrganizacionSeleccionadaScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanPago extends Model
{
    use HasFactory;
    use TieneOrganizacionSeleccionadaScope;

    protected $table = 'planes_pago';

    protected $fillable = [
        'organizacion_id',
        'asociado_id',
        'descripcion',
        'total',
        'estado',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function organizacion(): BelongsTo
    {
        return $this->belongsTo(Organizacion::class);
    }

    public function asociado(): BelongsTo
    {
        return $this->belongsTo(Asociado::class);
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class, 'plan_pago_id');
    }
}
