<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Asociado extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'organizacion_seleccionada_id',
        'google_id',
        'email_verified_at',
        'password',
    ];

    /**
     * Los atributos que deben ser ocultados en serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
        'es_admin' => 'boolean',
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
            ->withPivot('fecha_alta', 'fecha_baja', 'activo', 'es_admin')
            ->withTimestamps();
    }


    /**
     * Organización seleccionada del asociado (FK en la tabla asociados).
     */
    public function organizacionSeleccionada(): BelongsTo
    {
        return $this->belongsTo(Organizacion::class, 'organizacion_seleccionada_id');
    }

    /**
     * Accessor: $asociado->organizacion_seleccionada_id (ya lo expone Eloquent),
     * pero si quisieras un alias:
     */
    public function getOrganizacionSeleccionadaIdAttribute($value): ?int
    {
        return $value ? (int) $value : null;
    }
}
