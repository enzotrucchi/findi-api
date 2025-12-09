<?php

namespace App\Http\Requests\Proveedor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Request de validación para Proveedor
 * 
 * Maneja validaciones tanto para creación (POST) como para actualización (PUT/PATCH).
 */
class ProveedorRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:255', 'min:2'],
            'email' => ['required', 'email', 'max:255'],
            'telefono' => ['required', 'string', 'max:50'],
            'activo' => ['sometimes', 'boolean'],
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
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',

            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.max' => 'El email no puede tener más de 255 caracteres.',

            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.string' => 'El teléfono debe ser un texto.',
            'telefono.max' => 'El teléfono no puede tener más de 50 caracteres.',

            'activo.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
