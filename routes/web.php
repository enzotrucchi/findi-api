<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Ruta CSRF explícita para SPA (Angular/frontend separado)
// Esta ruta necesita estar en 'web' group para acceso a sesión,
// pero excluida del middleware CSRF verification
Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
})->middleware('web');

// Previews de emails (solo para desarrollo)
if (app()->environment(['local', 'development'])) {
    Route::prefix('mail-preview')->group(function () {
        Route::get('/resumen-mensual/{organizacionId?}', function ($organizacionId = null) {
            $organizacion = $organizacionId 
                ? \App\Models\Organizacion::findOrFail($organizacionId)
                : \App\Models\Organizacion::where('habilitada', true)->first();

            if (!$organizacion) {
                return 'No hay organizaciones disponibles para preview';
            }

            // Obtener un asociado activo de ejemplo para el preview
            $asociado = $organizacion->asociados()
                ->wherePivot('activo', true)
                ->first();

            if (!$asociado) {
                return 'No hay asociados activos en esta organización para preview';
            }

            $resumenService = app(\App\Services\ResumenMensualService::class);
            $totalizadores = $resumenService->obtenerTotalizadores($organizacion);

            $mailable = new \App\Mail\ResumenMensual(
                $organizacion, 
                $totalizadores,
                $asociado->nombre ?: 'Asociado'
            );

            return $mailable;
        });

        Route::get('/facturacion-mensual/{organizacionId?}', function ($organizacionId = null) {
            $facturacion = $organizacionId
                ? \App\Models\Facturacion::whereHas('organizacion', function ($q) use ($organizacionId) {
                    $q->where('id', $organizacionId);
                  })->latest()->firstOrFail()
                : \App\Models\Facturacion::latest()->first();

            if (!$facturacion) {
                return 'No hay facturaciones disponibles para preview';
            }

            $mailable = new \App\Mail\FacturacionMensual($facturacion);

            return $mailable;
        });
    });
}
