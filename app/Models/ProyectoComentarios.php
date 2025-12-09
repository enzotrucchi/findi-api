<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProyectoComentarios extends Model
{
    use HasFactory;

    protected $table = 'proyecto_comentarios';

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
