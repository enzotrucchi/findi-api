<?php

namespace App\Http\Requests\Movimiento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MovimientoMasivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'movimientos' => ['required', 'array', 'min:1'],

            'movimientos.*.tipo' => ['required', 'in:ingreso,egreso,inicial'],
            'movimientos.*.monto' => ['required', 'numeric', 'min:0'],
            'movimientos.*.fecha' => ['required', 'date'],
            'movimientos.*.detalle' => ['nullable', 'string', 'max:1000'],
            'movimientos.*.proyecto_id' => ['nullable', 'integer', 'exists:proyectos,id'],
            'movimientos.*.proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'movimientos.*.modo_pago_id' => ['required', 'integer', 'exists:modos_pago,id'],

            // asociado_id: requerido SOLO si tipo=ingreso, por item:
            'movimientos.*.asociado_id' => ['nullable', 'integer', 'exists:asociados,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $movimientos = $this->input('movimientos', []);

            foreach ($movimientos as $i => $m) {
                $tipo = $m['tipo'] ?? null;

                if ($tipo === 'ingreso' && empty($m['asociado_id'])) {
                    $validator->errors()->add("movimientos.$i.asociado_id", 'El asociado es obligatorio para movimientos de tipo ingreso.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'movimientos.required' => 'Debe enviar al menos un movimiento.',
            'movimientos.array' => 'El campo movimientos debe ser un array.',
            'movimientos.min' => 'Debe enviar al menos un movimiento.',

            'movimientos.*.tipo.required' => 'El tipo de movimiento es obligatorio.',
            'movimientos.*.tipo.in' => 'El tipo de movimiento debe ser "ingreso", "egreso" o "inicial".',

            'movimientos.*.monto.required' => 'El monto es obligatorio.',
            'movimientos.*.monto.numeric' => 'El monto debe ser un número.',
            'movimientos.*.monto.min' => 'El monto no puede ser negativo.',

            'movimientos.*.fecha.required' => 'La fecha es obligatoria.',
            'movimientos.*.fecha.date' => 'La fecha debe ser una fecha válida.',

            'movimientos.*.detalle.string' => 'La descripción debe ser un texto.',
            'movimientos.*.detalle.max' => 'La descripción no puede tener más de 1000 caracteres.',

            'movimientos.*.proyecto_id.integer' => 'El ID del proyecto debe ser un número entero.',
            'movimientos.*.proyecto_id.exists' => 'El proyecto especificado no existe.',

            'movimientos.*.asociado_id.integer' => 'El ID del asociado debe ser un número entero.',
            'movimientos.*.asociado_id.exists' => 'El asociado especificado no existe.',

            'movimientos.*.proveedor_id.integer' => 'El ID del proveedor debe ser un número entero.',
            'movimientos.*.proveedor_id.exists' => 'El proveedor especificado no existe.',

            'movimientos.*.modo_pago_id.required' => 'El modo de pago es obligatorio.',
            'movimientos.*.modo_pago_id.integer' => 'El ID del modo de pago debe ser un número entero.',
            'movimientos.*.modo_pago_id.exists' => 'El modo de pago especificado no existe.',
        ];
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
