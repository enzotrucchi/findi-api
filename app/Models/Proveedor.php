<?php

namespace App\Models;

use App\Models\Traits\TieneOrganizacionSeleccionadaScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Proveedor
 * 
 * Representa un proveedor en el sistema.
 * Este modelo solo define la estructura de la tabla,
 * sin lógica de negocio.
 */
class Proveedor extends Model
{
    use HasFactory;
    use TieneOrganizacionSeleccionadaScope;


    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'proveedores';

    /**
     * Atributos asignables en masa.
     *
     * @var array<string>
     */
    protected $fillable = [
        'organizacion_id',
        'nombre',
        'email',
        'telefono',
        'activo',
    ];

    /**
     * Atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con Organizacion.
     */
    public function organizacion()
    {
        return $this->belongsTo(Organizacion::class);
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'proveedor_id');
    }
}
