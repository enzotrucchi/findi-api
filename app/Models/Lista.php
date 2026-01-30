<?php

namespace App\Models;

use App\Models\Traits\TieneOrganizacionSeleccionadaScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lista extends Model
{
    use HasFactory;
    use TieneOrganizacionSeleccionadaScope;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'organizacion_id',
        'color',
    ];

    /**
     * Obtener la organización de la lista.
     *
     * @return BelongsTo
     */
    public function organizacion(): BelongsTo
    {
        return $this->belongsTo(Organizacion::class);
    }

    /**
     * Obtener los asociados de la lista.
     *
     * @return BelongsToMany
     */
    public function asociados(): BelongsToMany
    {
        return $this->belongsToMany(Asociado::class, 'asociado_lista')
            ->withTimestamps();
    }
}
