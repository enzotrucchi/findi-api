<?php

use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\AsociadoController;
use App\Http\Controllers\Api\MovimientoController;
use App\Http\Controllers\Api\OrganizacionController;
use App\Http\Controllers\Api\ProyectoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CambiarOrganizacionSeleccionadaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Asociado;
/*
|--------------------------------------------------------------------------
| Rutas de la API
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas de la API. Estas rutas son cargadas por
| el RouteServiceProvider y todas tienen asignado el middleware group "api".
|
*/

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación
|--------------------------------------------------------------------------
*/

Route::post('auth/google', [AuthController::class, 'googleLogin']);

Route::post('auth/crear-cuenta', [AuthController::class, 'crearCuenta']);


Route::post('auth/dev-login', function (Request $request) {
    abort_unless(app()->environment('local'), 403, 'Solo disponible en entorno local.');

    $request->validate([
        'email'    => 'required|email',
        'org_id'   => 'nullable|integer',
    ]);

    /** @var Asociado|null $user */
    $user = Asociado::where('email', $request->input('email'))->firstOrFail();

    // Si viene org_id, y el usuario pertenece a esa org activa, la seteamos como seleccionada
    if ($orgId = $request->input('org_id')) {
        $pertenece = $user->organizaciones()
            ->where('organizacion_id', $orgId)
            ->wherePivot('activo', true)
            ->exists();

        abort_unless($pertenece, 403, 'No pertenece a esa organización activa.');

        $user->organizacion_seleccionada_id = $orgId;
        $user->save();
    }

    $token = $user->createToken('dev-login')->plainTextToken;

    return response()->json([
        'usuario'  => [
            'id'     => $user->id,
            'nombre' => $user->nombre,
            'email'  => $user->email,
        ],
        'token'    => $token,
        'organizacion_seleccionada_id' => $user->organizacion_seleccionada_id,
    ]);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('organizaciones/{organizacion}/seleccionar', CambiarOrganizacionSeleccionadaController::class);


    /*
|--------------------------------------------------------------------------
| Rutas de Proveedores
|--------------------------------------------------------------------------
*/

    Route::get('proveedores', [ProveedorController::class, 'obtenerColeccion']);
    Route::post('proveedores', [ProveedorController::class, 'crear']);
    Route::put('proveedores/{id}', [ProveedorController::class, 'actualizar']);
    Route::delete('proveedores/{id}', [ProveedorController::class, 'eliminar']);

    // Route::get('proveedores/{id}', [ProveedorController::class, 'obtener']);
    // Route::patch('proveedores/{id}', [ProveedorController::class, 'actualizar']);

    /*
|--------------------------------------------------------------------------
| Rutas de Asociados
|--------------------------------------------------------------------------
*/

    Route::get('asociados/estadisticas', [AsociadoController::class, 'obtenerEstadisticas']);
    Route::get('asociados', [AsociadoController::class, 'obtenerColeccion']);
    Route::post('asociados', [AsociadoController::class, 'crear']);
    Route::put('asociados/{id}', [AsociadoController::class, 'actualizar']);
    Route::patch('asociados/{id}/activar', [AsociadoController::class, 'activar']);
    Route::patch('asociados/{id}/desactivar', [AsociadoController::class, 'desactivar']);
    Route::delete('asociados/{id}', [AsociadoController::class, 'eliminar']);

    /*
|--------------------------------------------------------------------------
| Rutas de Movimientos
|--------------------------------------------------------------------------
*/

    Route::get('movimientos/balance', [MovimientoController::class, 'obtenerBalance']);
    Route::get('movimientos', [MovimientoController::class, 'obtenerColeccion']);
    Route::post('movimientos', [MovimientoController::class, 'crear']);
    Route::put('movimientos/{id}', [MovimientoController::class, 'actualizar']);
    Route::delete('movimientos/{id}', [MovimientoController::class, 'eliminar']);

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

    Route::get('proyectos/estadisticas', [ProyectoController::class, 'obtenerEstadisticas']);

    Route::get('proyectos', [ProyectoController::class, 'obtenerColeccion']);
    Route::post('proyectos', [ProyectoController::class, 'crear']);
    Route::put('proyectos/{id}', [ProyectoController::class, 'actualizar']);
    Route::delete('proyectos/{id}', [ProyectoController::class, 'eliminar']);

    // Route::get('proyectos/{id}', [ProyectoController::class, 'obtener']);
    // Route::patch('proyectos/{id}', [ProyectoController::class, 'actualizar']);
    // Route::get('proyectos/buscar', [ProyectoController::class, 'buscar']);
    // Route::get('proyectos/contar', [ProyectoController::class, 'contar']);
    // Route::post('proyectos/por-ids', [ProyectoController::class, 'obtenerPorIds']);
    // Route::get('proyectos/existe/{id}', [ProyectoController::class, 'existe']);
});
