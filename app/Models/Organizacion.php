<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizacion extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla.
     *
     * @var string
     */
    protected $table = 'organizaciones';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'admin_email',
        'admin_nombre',
        'fecha_alta',
        'es_prueba',
        'fecha_fin_prueba',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'es_prueba' => 'boolean',
    ];

    /**
     * Obtener los movimientos de la organización.
     *
     * @return HasMany
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }
}
