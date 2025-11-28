<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyecto extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'descripcion',
        'monto_actual',
        'monto_objetivo',
        'fecha_alta',
        'fecha_realizacion',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'monto_actual' => 'decimal:2',
        'monto_objetivo' => 'decimal:2',
    ];

    /**
     * Obtener los movimientos del proyecto.
     *
     * @return HasMany
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    /**
     * Historial / comentarios del proyecto.
     */
    public function historial(): HasMany
    {
        return $this->hasMany(ProyectoHistorial::class);
    }
}
