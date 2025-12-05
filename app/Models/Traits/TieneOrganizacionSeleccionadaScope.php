<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait TieneOrganizacionSeleccionadaScope
{
    protected static function bootTieneOrganizacionSeleccionadaScope(): void
    {
        static::addGlobalScope('organizacion_seleccionada', function (Builder $builder) {
            if (app()->runningInConsole()) {
                return;
            }

            $user = Auth::guard('sanctum')->user() ?? Auth::user();

            if (! $user instanceof \App\Models\Asociado) {
                return;
            }

            $orgId = $user->organizacion_seleccionada_id;

            if (! $orgId) {
                abort(403, 'No hay organización seleccionada.');
            }

            /** @var Model $model */
            $model = $builder->getModel();
            $table = $model->getTable();

            $builder->where($table . '.organizacion_id', $orgId);
        });
    }
}
