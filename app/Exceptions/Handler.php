<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Lista de inputs que nunca se envían a la sesión en excepciones de validación.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Registrar callbacks de manejo de excepciones.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Renderizar excepciones como respuestas JSON para la API.
     *
     * @param \Illuminate\Http\Request $request
     * @param Throwable $e
     * @return \Illuminate\Http\Response|JsonResponse
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        // Solo manejar excepciones para rutas de API
        if ($request->is('api/*')) {
            return $this->manejarExcepcionApi($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Manejar excepciones específicas de la API.
     *
     * @param \Illuminate\Http\Request $request
     * @param Throwable $e
     * @return JsonResponse
     */
    private function manejarExcepcionApi($request, Throwable $e): JsonResponse
    {
        // Modelo no encontrado
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Recurso no encontrado.',
            ], 404);
        }

        // Ruta no encontrada
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Endpoint no encontrado.',
            ], 404);
        }

        // Validación fallida
        if ($e instanceof ValidationException) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Errores de validación.',
                'errores' => $e->errors(),
            ], 422);
        }

        // Error genérico
        $codigoEstado = method_exists($e, 'getStatusCode') 
            ? $e->getStatusCode() 
            : 500;

        $mensaje = config('app.debug') 
            ? $e->getMessage() 
            : 'Error interno del servidor.';

        return response()->json([
            'exito' => false,
            'mensaje' => $mensaje,
        ], $codigoEstado);
    }
}
