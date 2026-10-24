<?php

use App\Http\Controllers\Api\AsociadoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListaController;
use App\Http\Controllers\Api\MovimientoController;
use App\Http\Controllers\Api\OrganizacionController;
use App\Http\Controllers\Api\PlanPagoController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\ProyectoComentarioController;
use App\Http\Controllers\Api\ProyectoController;
use App\Models\Asociado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de la API
|--------------------------------------------------------------------------
|
| Niveles de acceso:
| - públicas: login con Google y alta de cuenta (con rate limit).
| - auth:sanctum: elegir organización y validar el código de acceso.
| - org.activa: miembro activo de una organización habilitada (lectura).
| - org.admin: administrador de la organización seleccionada.
|
*/

Route::middleware('throttle:auth')->group(function () {
    Route::post('auth/google', [AuthController::class, 'googleLogin']);
    Route::post('auth/crear-cuenta', [AuthController::class, 'crearCuenta']);
});

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

    // Accesibles aunque la organización seleccionada esté deshabilitada o falte el código.
    Route::post('organizaciones/seleccionar', [AuthController::class, 'seleccionarOrganizacion']);
    Route::post('auth/validar-codigo-acceso', [AuthController::class, 'validarCodigoAcceso'])
        ->middleware('throttle:codigo-acceso');
    Route::get('organizaciones', [OrganizacionController::class, 'obtenerColeccion']);
    Route::put('mi-perfil', [AsociadoController::class, 'actualizarPerfil']);

    Route::middleware('org.activa')->group(function () {

        /*
        |----------------------------------------------------------------------
        | Lectura para cualquier miembro
        |----------------------------------------------------------------------
        */
        Route::get('organizaciones/{id}', [OrganizacionController::class, 'obtener'])->whereNumber('id');
        Route::get('movimientos', [MovimientoController::class, 'obtenerColeccion']);
        Route::get('movimientos/balance', [MovimientoController::class, 'obtenerBalance']);
        Route::get('movimientos/{id}/comprobante', [MovimientoController::class, 'descargarComprobante'])->whereNumber('id');
        Route::get('asociados/estadisticas', [AsociadoController::class, 'obtenerEstadisticas']);
        Route::get('proyectos/estadisticas', [ProyectoController::class, 'obtenerEstadisticas']);

        Route::middleware('org.admin')->group(function () {

            /*
            |------------------------------------------------------------------
            | Organización
            |------------------------------------------------------------------
            */
            Route::put('organizaciones/{id}', [OrganizacionController::class, 'actualizar'])->whereNumber('id');
            Route::get('organizaciones/codigo-acceso', [OrganizacionController::class, 'obtenerCodigoAcceso']);
            Route::put('organizaciones/codigo-acceso', [OrganizacionController::class, 'actualizarCodigoAcceso']);
            Route::post('organizaciones/codigo-acceso/regenerar', [OrganizacionController::class, 'regenerarCodigoAcceso']);

            /*
            |------------------------------------------------------------------
            | Proveedores
            |------------------------------------------------------------------
            */
            Route::get('proveedores/movimientos', [ProveedorController::class, 'obtenerMovimientos']);
            Route::get('proveedores', [ProveedorController::class, 'obtenerColeccion']);
            Route::post('proveedores', [ProveedorController::class, 'crear']);
            Route::put('proveedores/{id}', [ProveedorController::class, 'actualizar'])->whereNumber('id');
            Route::delete('proveedores/{id}', [ProveedorController::class, 'eliminar'])->whereNumber('id');

            /*
            |------------------------------------------------------------------
            | Asociados
            |------------------------------------------------------------------
            */
            Route::get('asociados/todos', [AsociadoController::class, 'todos']);
            Route::get('asociados', [AsociadoController::class, 'obtenerColeccion']);
            Route::get('asociados/{id}', [AsociadoController::class, 'obtener'])->whereNumber('id');
            Route::post('asociados', [AsociadoController::class, 'crear']);
            Route::get('asociados/{id}/movimientos', [AsociadoController::class, 'obtenerMovimientos'])->whereNumber('id');
            Route::put('asociados/{id}', [AsociadoController::class, 'actualizar'])->whereNumber('id');
            Route::patch('asociados/{id}/activar', [AsociadoController::class, 'activar'])->whereNumber('id');
            Route::patch('asociados/{id}/desactivar', [AsociadoController::class, 'desactivar'])->whereNumber('id');
            Route::delete('asociados/{id}', [AsociadoController::class, 'eliminar'])->whereNumber('id');

            /*
            |------------------------------------------------------------------
            | Movimientos
            |------------------------------------------------------------------
            */
            Route::post('movimientos', [MovimientoController::class, 'crear']);
            Route::post('movimientos/carga-masiva', [MovimientoController::class, 'cargaMasiva']);
            Route::put('movimientos/{id}', [MovimientoController::class, 'actualizar'])->whereNumber('id');
            Route::delete('movimientos/{id}', [MovimientoController::class, 'eliminar'])->whereNumber('id');

            /*
            |------------------------------------------------------------------
            | Planes de pago
            |------------------------------------------------------------------
            */
            Route::get('planes-pago', [PlanPagoController::class, 'obtenerColeccion']);
            Route::post('planes-pago', [PlanPagoController::class, 'crear']);
            Route::get('planes-pago/{id}', [PlanPagoController::class, 'obtener'])->whereNumber('id');
            Route::post('planes-pago/cuotas/{id}/cancelar', [PlanPagoController::class, 'cancelarCuota'])->whereNumber('id');

            /*
            |------------------------------------------------------------------
            | Proyectos y comentarios
            |------------------------------------------------------------------
            */
            Route::get('proyectos', [ProyectoController::class, 'obtenerColeccion']);
            Route::post('proyectos', [ProyectoController::class, 'crear']);
            Route::put('proyectos/{id}', [ProyectoController::class, 'actualizar'])->whereNumber('id');
            Route::delete('proyectos/{id}', [ProyectoController::class, 'eliminar'])->whereNumber('id');
            Route::get('proyectos/{id}/movimientos', [ProyectoController::class, 'obtenerMovimientos'])->whereNumber('id');

            Route::get('proyectos/{id}/comentarios', [ProyectoComentarioController::class, 'obtenerComentarios'])->whereNumber('id');
            Route::post('proyectos/{id}/comentarios', [ProyectoComentarioController::class, 'agregarComentario'])->whereNumber('id');
            Route::put('proyectos/{proyectoId}/comentarios/{comentarioId}', [ProyectoComentarioController::class, 'actualizarComentario'])
                ->whereNumber(['proyectoId', 'comentarioId']);
            Route::delete('proyectos/{proyectoId}/comentarios/{comentarioId}', [ProyectoComentarioController::class, 'eliminarComentario'])
                ->whereNumber(['proyectoId', 'comentarioId']);

            /*
            |------------------------------------------------------------------
            | Listas
            |------------------------------------------------------------------
            */
            Route::get('listas/todas', [ListaController::class, 'todas']);
            Route::get('listas', [ListaController::class, 'index']);
            Route::post('listas', [ListaController::class, 'store']);
            Route::get('listas/{id}', [ListaController::class, 'show'])->whereNumber('id');
            Route::put('listas/{id}', [ListaController::class, 'update'])->whereNumber('id');
            Route::delete('listas/{id}', [ListaController::class, 'destroy'])->whereNumber('id');
            Route::get('listas/{id}/asociados', [ListaController::class, 'asociados'])->whereNumber('id');
            Route::post('listas/{id}/asociados', [ListaController::class, 'agregarAsociados'])->whereNumber('id');
            Route::put('listas/{id}/asociados', [ListaController::class, 'reemplazarAsociados'])->whereNumber('id');
            Route::delete('listas/{id}/asociados/{asociadoId}', [ListaController::class, 'eliminarAsociado'])
                ->whereNumber(['id', 'asociadoId']);
        });
    });
});
