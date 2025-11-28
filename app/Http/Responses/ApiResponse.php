<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Helper para respuestas estandarizadas de la API
 * 
 * Proporciona métodos estáticos para crear respuestas JSON consistentes.
 */
class ApiResponse
{
    /**
     * Crear una respuesta exitosa.
     *
     * @param mixed $datos
     * @param string $mensaje
     * @param int $codigoEstado
     * @return JsonResponse
     */
    public static function exito(
        mixed $datos = null, 
        string $mensaje = 'Operación exitosa', 
        int $codigoEstado = 200
    ): JsonResponse {
        return response()->json([
            'exito' => true,
            'mensaje' => $mensaje,
            'datos' => $datos,
        ], $codigoEstado);
    }

    /**
     * Crear una respuesta de error.
     *
     * @param string $mensaje
     * @param int $codigoEstado
     * @param mixed $errores
     * @return JsonResponse
     */
    public static function error(
        string $mensaje = 'Error en la operación', 
        int $codigoEstado = 400,
        mixed $errores = null
    ): JsonResponse {
        $respuesta = [
            'exito' => false,
            'mensaje' => $mensaje,
        ];

        if ($errores !== null) {
            $respuesta['errores'] = $errores;
        }

        return response()->json($respuesta, $codigoEstado);
    }

    /**
     * Crear una respuesta para recurso creado.
     *
     * @param mixed $datos
     * @param string $mensaje
     * @return JsonResponse
     */
    public static function creado(
        mixed $datos, 
        string $mensaje = 'Recurso creado exitosamente'
    ): JsonResponse {
        return self::exito($datos, $mensaje, 201);
    }

    /**
     * Crear una respuesta para recurso no encontrado.
     *
     * @param string $mensaje
     * @return JsonResponse
     */
    public static function noEncontrado(
        string $mensaje = 'Recurso no encontrado'
    ): JsonResponse {
        return self::error($mensaje, 404);
    }

    /**
     * Crear una respuesta para validación fallida.
     *
     * @param mixed $errores
     * @param string $mensaje
     * @return JsonResponse
     */
    public static function validacionFallida(
        mixed $errores,
        string $mensaje = 'Errores de validación'
    ): JsonResponse {
        return self::error($mensaje, 422, $errores);
    }

    /**
     * Crear una respuesta para operación sin contenido.
     *
     * @return JsonResponse
     */
    public static function sinContenido(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
