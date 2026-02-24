<?php

namespace App\Http\Requests\PlanPago;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class GetPlanesPagoRequest extends FormRequest
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
        return [
            'pagina' => ['nullable', 'integer', 'min:1'],
            'asociado_id' => ['nullable', 'integer', 'exists:asociados,id'],
            'estado' => ['nullable', Rule::in(['activo', 'finalizado', 'cancelado'])],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('pagina')) {
            $this->merge(['pagina' => 1]);
        }
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
