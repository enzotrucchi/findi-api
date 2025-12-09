<?php

namespace App\Http\Requests\Proyecto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Request de validación para Proyecto
 * 
 * Maneja validaciones tanto para creación (POST) como para actualización (PUT/PATCH).
 */
class ProyectoRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para hacer este request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Sin autenticación por ahora
        return true;
    }

    /**
     * Reglas de validación.
     * Se ajustan automáticamente según el método HTTP.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'descripcion' => ['required', 'string', 'min:2'],
            'monto_actual' => ['nullable', 'numeric', 'min:0'],
            'monto_objetivo' => ['required', 'numeric', 'min:0'],
            'fecha_alta' => ['required', 'date'],
            'fecha_realizacion' => ['nullable', 'date', 'after_or_equal:fecha_alta'],
        ];
    }

    /**
     * Mensajes personalizados de validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción debe ser un texto.',
            'descripcion.min' => 'La descripción debe tener al menos 2 caracteres.',

            'monto_actual.numeric' => 'El monto actual debe ser un número.',
            'monto_actual.min' => 'El monto actual no puede ser negativo.',

            'monto_objetivo.required' => 'El monto objetivo es obligatorio.',
            'monto_objetivo.numeric' => 'El monto objetivo debe ser un número.',
            'monto_objetivo.min' => 'El monto objetivo no puede ser negativo.',

            'fecha_alta.required' => 'La fecha de alta es obligatoria.',
            'fecha_alta.date' => 'La fecha de alta debe ser una fecha válida.',

            'fecha_realizacion.date' => 'La fecha de realización debe ser una fecha válida.',
            'fecha_realizacion.after_or_equal' => 'La fecha de realización debe ser igual o posterior a la fecha de alta.',
        ];
    }
}
