<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProyectoHistorial extends Model
{
    use HasFactory;

    // Porque la tabla no sigue el plural "historials"
    protected $table = 'proyecto_historial';

    protected $fillable = [
        'proyecto_id',
        'asociado_id',
        'detalle',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function asociado(): BelongsTo
    {
        return $this->belongsTo(Asociado::class);
    }
}
