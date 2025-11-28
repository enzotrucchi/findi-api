<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Asociado extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'domicilio',
        'es_admin',
        'activo',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'es_admin' => 'boolean',
        'activo' => 'boolean',
    ];

    /**
     * Obtener los movimientos del asociado.
     *
     * @return HasMany
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    /**
     * Obtener las organizaciones del asociado.
     *
     * @return BelongsToMany
     */
    public function organizaciones(): BelongsToMany
    {
        return $this->belongsToMany(Organizacion::class, 'asociado_organizacion')
            ->withPivot('fecha_alta', 'fecha_baja', 'activo')
            ->withTimestamps();
    }
}
