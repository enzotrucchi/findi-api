<?php

use App\Http\Controllers\Api\ProveedorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de la API
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas de la API. Estas rutas son cargadas por
| el RouteServiceProvider y todas tienen asignado el middleware group "api".
|
*/

// Ruta de prueba con autenticación (comentada por ahora)
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

/*
|--------------------------------------------------------------------------
| Rutas de Proveedores
|--------------------------------------------------------------------------
*/
Route::controller(ProveedorController::class)->group(function () {
    Route::get('proveedores', 'obtenerColeccion');
    Route::post('proveedores', 'crear');
    Route::get('proveedores/{id}', 'obtener');
    Route::put('proveedores/{id}', 'actualizar');
    Route::patch('proveedores/{id}', 'actualizar');
    Route::delete('proveedores/{id}', 'eliminar');
});
