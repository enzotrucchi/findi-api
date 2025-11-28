<?php

use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\AsociadoController;
use App\Http\Controllers\Api\MovimientoController;
use App\Http\Controllers\Api\OrganizacionController;
use App\Http\Controllers\Api\ProyectoController;
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

Route::get('proveedores', [ProveedorController::class, 'obtenerColeccion']);
Route::post('proveedores', [ProveedorController::class, 'crear']);
Route::get('proveedores/{id}', [ProveedorController::class, 'obtener']);
Route::put('proveedores/{id}', [ProveedorController::class, 'actualizar']);
Route::patch('proveedores/{id}', [ProveedorController::class, 'actualizar']);
Route::delete('proveedores/{id}', [ProveedorController::class, 'eliminar']);

/*
|--------------------------------------------------------------------------
| Rutas de Asociados
|--------------------------------------------------------------------------
*/

Route::get('asociados', [AsociadoController::class, 'obtenerColeccion']);
Route::post('asociados', [AsociadoController::class, 'crear']);
Route::get('asociados/{id}', [AsociadoController::class, 'obtener']);
Route::put('asociados/{id}', [AsociadoController::class, 'actualizar']);
Route::patch('asociados/{id}', [AsociadoController::class, 'actualizar']);
Route::delete('asociados/{id}', [AsociadoController::class, 'eliminar']);
Route::get('asociados/buscar', [AsociadoController::class, 'buscar']);
Route::get('asociados/contar', [AsociadoController::class, 'contar']);
Route::post('asociados/por-ids', [AsociadoController::class, 'obtenerPorIds']);
Route::get('asociados/existe/{id}', [AsociadoController::class, 'existe']);

/*
|--------------------------------------------------------------------------
| Rutas de Movimientos
|--------------------------------------------------------------------------
*/

Route::get('movimientos', [MovimientoController::class, 'obtenerColeccion']);
Route::post('movimientos', [MovimientoController::class, 'crear']);
Route::get('movimientos/{id}', [MovimientoController::class, 'obtener']);
Route::put('movimientos/{id}', [MovimientoController::class, 'actualizar']);
Route::patch('movimientos/{id}', [MovimientoController::class, 'actualizar']);
Route::delete('movimientos/{id}', [MovimientoController::class, 'eliminar']);
Route::get('movimientos/buscar', [MovimientoController::class, 'buscar']);
Route::get('movimientos/contar', [MovimientoController::class, 'contar']);
Route::post('movimientos/por-ids', [MovimientoController::class, 'obtenerPorIds']);
Route::get('movimientos/existe/{id}', [MovimientoController::class, 'existe']);

/*
|--------------------------------------------------------------------------
| Rutas de Organizaciones
|--------------------------------------------------------------------------
*/

Route::get('organizaciones', [OrganizacionController::class, 'obtenerColeccion']);
Route::post('organizaciones', [OrganizacionController::class, 'crear']);
Route::get('organizaciones/{id}', [OrganizacionController::class, 'obtener']);
Route::put('organizaciones/{id}', [OrganizacionController::class, 'actualizar']);
Route::patch('organizaciones/{id}', [OrganizacionController::class, 'actualizar']);
Route::delete('organizaciones/{id}', [OrganizacionController::class, 'eliminar']);
Route::get('organizaciones/buscar', [OrganizacionController::class, 'buscar']);
Route::get('organizaciones/contar', [OrganizacionController::class, 'contar']);
Route::post('organizaciones/por-ids', [OrganizacionController::class, 'obtenerPorIds']);
Route::get('organizaciones/existe/{id}', [OrganizacionController::class, 'existe']);

/*
|--------------------------------------------------------------------------
| Rutas de Proyectos
|--------------------------------------------------------------------------
*/

Route::get('proyectos', [ProyectoController::class, 'obtenerColeccion']);
Route::post('proyectos', [ProyectoController::class, 'crear']);
Route::get('proyectos/{id}', [ProyectoController::class, 'obtener']);
Route::put('proyectos/{id}', [ProyectoController::class, 'actualizar']);
Route::patch('proyectos/{id}', [ProyectoController::class, 'actualizar']);
Route::delete('proyectos/{id}', [ProyectoController::class, 'eliminar']);
Route::get('proyectos/buscar', [ProyectoController::class, 'buscar']);
Route::get('proyectos/contar', [ProyectoController::class, 'contar']);
Route::post('proyectos/por-ids', [ProyectoController::class, 'obtenerPorIds']);
Route::get('proyectos/existe/{id}', [ProyectoController::class, 'existe']);
