<?php

namespace App\Http\Requests\PlanPago;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CancelarCuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

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
