<?php

namespace App\Http\Requests\Lista;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Request de validación para asociar asociados a una lista
 */
class AsociadosListaRequest extends FormRequest
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
            'asociado_ids' => ['required', 'array', 'min:1'],
            'asociado_ids.*' => ['required', 'integer', 'exists:asociados,id'],
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
            'asociado_ids.required' => 'Debe proporcionar al menos un asociado.',
            'asociado_ids.array' => 'Los IDs de asociados deben ser un arreglo.',
            'asociado_ids.min' => 'Debe proporcionar al menos un asociado.',
            'asociado_ids.*.required' => 'Cada ID de asociado es obligatorio.',
            'asociado_ids.*.integer' => 'Cada ID de asociado debe ser un número entero.',
            'asociado_ids.*.exists' => 'Uno o más asociados no existen.',
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
