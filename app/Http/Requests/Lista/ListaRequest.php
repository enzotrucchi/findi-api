<?php

namespace App\Http\Requests\Lista;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Request de validación para Lista
 * 
 * Maneja validaciones tanto para creación (POST) como para actualización (PUT/PATCH).
 */
class ListaRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para hacer este request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
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
            'nombre.required' => 'El nombre de la lista es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'descripcion.string' => 'La descripción debe ser un texto.',
            'descripcion.max' => 'La descripción no puede tener más de 1000 caracteres.',
            'color.string' => 'El color debe ser un texto.',
            'color.regex' => 'El color debe ser un código hexadecimal válido (ej: #FF5733).',
        ];
    }

    /**
     * Manejar fallos de validación.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'exito' => false,
                'mensaje' => 'Errores de validación.',
                'errores' => $validator->errors(),
            ], 422)
        );
    }
}
