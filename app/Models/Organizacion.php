<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'fecha_alta',
        'es_prueba',
        'fecha_fin_prueba',
        'fecha_vencimiento',
        'habilitada',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'es_prueba' => 'boolean',
        'habilitada' => 'boolean',
        'fecha_vencimiento' => 'date',
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

    /**
     * Obtener los asociados de la organización.
     *
     * @return BelongsToMany
     */
    public function asociados(): BelongsToMany
    {
        return $this->belongsToMany(Asociado::class, 'asociado_organizacion')
            ->withPivot('fecha_alta', 'fecha_baja', 'activo')
            ->withTimestamps();
    }

    /**
     * Obtener las facturas de la organización.
     *
     * @return HasMany
     */
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    /**
     * Obtener la facturación de la organización.
     *
     * @return HasMany
     */
    public function facturacion(): HasMany
    {
        return $this->hasMany(Facturacion::class);
    }

    /**
     * Verificar si la organización está habilitada.
     *
     * @return bool
     */
    public function estaHabilitada(): bool
    {
        return $this->habilitada;
    }

    /**
     * Verificar si la organización tiene acceso (habilitada y no vencida).
     *
     * @return bool
     */
    public function tieneAcceso(): bool
    {
        // Debe estar habilitada
        if (!$this->habilitada) {
            return false;
        }

        // Si es prueba y no ha vencido
        if ($this->es_prueba && $this->fecha_fin_prueba && now()->lte($this->fecha_fin_prueba)) {
            return true;
        }

        // Si no es prueba, verificar fecha de vencimiento
        if (!$this->es_prueba && $this->fecha_vencimiento && now()->lte($this->fecha_vencimiento)) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si la organización está vencida.
     *
     * @return bool
     */
    public function estaVencida(): bool
    {
        if ($this->es_prueba && $this->fecha_fin_prueba) {
            return now()->isAfter($this->fecha_fin_prueba);
        }

        if (!$this->es_prueba && $this->fecha_vencimiento) {
            return now()->isAfter($this->fecha_vencimiento);
        }

        return false;
    }

    /**
     * Obtener la cantidad de asociados activos.
     *
     * @return int
     */
    public function cantidadAsociadosActivos(): int
    {
        return $this->asociados()->wherePivot('activo', true)->count();
    }

    /**
     * Deshabilitar la organización.
     *
     * @return void
     */
    public function deshabilitar(): void
    {
        $this->update(['habilitada' => false]);
    }

    /**
     * Habilitar la organización.
     *
     * @return void
     */
    public function habilitar(): void
    {
        $this->update(['habilitada' => true]);
    }
}
