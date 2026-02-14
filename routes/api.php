<?php

use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\AsociadoController;
use App\Http\Controllers\Api\MovimientoController;
use App\Http\Controllers\Api\OrganizacionController;
use App\Http\Controllers\Api\ProyectoController;
use App\Http\Controllers\Api\ProyectoComentarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CambiarOrganizacionSeleccionadaController;
use App\Http\Controllers\Api\ListaController;
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


// Rutas que permiten al usuario cambiar la organización seleccionada.
// Estas rutas sólo requieren autenticación y deben permanecer accesibles
// incluso si la organización actualmente seleccionada está deshabilitada.
// Route::post('organizaciones/{organizacion}/seleccionar', CambiarOrganizacionSeleccionadaController::class)
//     ->middleware('auth:sanctum');

Route::post('organizaciones/seleccionar', [AuthController::class, 'seleccionarOrganizacion'])
    ->middleware('auth:sanctum');

Route::post('auth/validar-codigo-acceso', [AuthController::class, 'validarCodigoAcceso'])
    ->middleware('auth:sanctum');

// Grupo principal: requiere autenticación
Route::middleware('auth:sanctum')->group(function () {

    Route::put('mi-perfil', [AsociadoController::class, 'actualizarPerfil']);
    /*
|--------------------------------------------------------------------------
| Rutas de Proveedores
|--------------------------------------------------------------------------
*/

    Route::get('proveedores/movimientos', [ProveedorController::class, 'obtenerMovimientos']);
    Route::get('proveedores', [ProveedorController::class, 'obtenerColeccion']);
    Route::post('proveedores', [ProveedorController::class, 'crear']);
    Route::put('proveedores/{id}', [ProveedorController::class, 'actualizar']);
    Route::delete('proveedores/{id}', [ProveedorController::class, 'eliminar']);

    /*
|--------------------------------------------------------------------------
| Rutas de Asociados
|--------------------------------------------------------------------------
*/

    Route::get('asociados/estadisticas', [AsociadoController::class, 'obtenerEstadisticas']);
    Route::get('asociados/todos', [AsociadoController::class, 'todos']);
    Route::get('asociados', [AsociadoController::class, 'obtenerColeccion']);
    Route::get('asociados/{id}', [AsociadoController::class, 'obtener']);
    Route::post('asociados', [AsociadoController::class, 'crear']);
    Route::get('asociados/{id}/movimientos', [AsociadoController::class, 'obtenerMovimientos']);
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
    Route::get('movimientos/{id}/comprobante', [MovimientoController::class, 'descargarComprobante']);
    Route::get('movimientos', [MovimientoController::class, 'obtenerColeccion']);
    Route::post('movimientos', [MovimientoController::class, 'crear']);
    Route::post('movimientos/carga-masiva', [MovimientoController::class, 'cargaMasiva']);
    Route::put('movimientos/{id}', [MovimientoController::class, 'actualizar']);
    Route::delete('movimientos/{id}', [MovimientoController::class, 'eliminar']);

    /*
|--------------------------------------------------------------------------
| Rutas de Organizaciones
|--------------------------------------------------------------------------
*/
    // Route::middleware('auth:sanctum')->post('auth/seleccionar-organizacion', [AuthController::class, 'seleccionarOrganizacion']);
    Route::get('organizaciones', [OrganizacionController::class, 'obtenerColeccion']);
    Route::post('organizaciones', [OrganizacionController::class, 'crear']);
    Route::get('organizaciones/codigo-acceso', [OrganizacionController::class, 'obtenerCodigoAcceso']);
    Route::put('organizaciones/codigo-acceso', [OrganizacionController::class, 'actualizarCodigoAcceso']);
    Route::post('organizaciones/codigo-acceso/regenerar', [OrganizacionController::class, 'regenerarCodigoAcceso']);
    Route::get('organizaciones/{id}', [OrganizacionController::class, 'obtener']);
    Route::put('organizaciones/{id}', [OrganizacionController::class, 'actualizar']);
    Route::delete('organizaciones/{id}', [OrganizacionController::class, 'eliminar']);

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

    /**
     * Rutas adicionales de Proyectos: 
     * Comentarios
     * Movimientos por proyecto
     */
    Route::get('proyectos/{id}/comentarios', [ProyectoComentarioController::class, 'obtenerComentarios']);
    Route::post('proyectos/{id}/comentarios', [ProyectoComentarioController::class, 'agregarComentario']);
    Route::put('proyectos/{proyectoId}/comentarios/{comentarioId}', [ProyectoComentarioController::class, 'actualizarComentario']);
    Route::delete('proyectos/{proyectoId}/comentarios/{comentarioId}', [ProyectoComentarioController::class, 'eliminarComentario']);

    Route::get('proyectos/{id}/movimientos', [ProyectoController::class, 'obtenerMovimientos']);

    /*
|--------------------------------------------------------------------------
| Rutas de Listas
|--------------------------------------------------------------------------
*/

    Route::get('listas/todas', [ListaController::class, 'todas']);
    Route::get('listas', [ListaController::class, 'index']);
    Route::post('listas', [ListaController::class, 'store']);
    Route::get('listas/{id}', [ListaController::class, 'show']);
    Route::put('listas/{id}', [ListaController::class, 'update']);
    Route::delete('listas/{id}', [ListaController::class, 'destroy']);
    Route::get('listas/{id}/asociados', [ListaController::class, 'asociados']);
    Route::post('listas/{id}/asociados', [ListaController::class, 'agregarAsociados']);
    Route::put('listas/{id}/asociados', [ListaController::class, 'reemplazarAsociados']);
    Route::delete('listas/{id}/asociados/{asociadoId}', [ListaController::class, 'eliminarAsociado']);
});
