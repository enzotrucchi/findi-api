<?php

namespace App\Http\Requests\Movimiento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Request de validación para Movimiento
 * 
 * Maneja validaciones tanto para creación (POST) como para actualización (PUT/PATCH).
 */
class MovimientoRequest extends FormRequest
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
        $esCreacion = $this->isMethod('POST');

        //Asociado es obligatorio solo en INGRESOS
        $asociadoRequerido = ($this->input('tipo') === 'ingreso') ? 'required' : 'nullable';


        return [
            'tipo' => ['required', 'in:ingreso,egreso,inicial'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha' => ['required', 'date'],
            'detalle' => ['nullable', 'string', 'max:1000'],
            'proyecto_id' => ['nullable', 'integer', 'exists:proyectos,id'],
            'asociado_id' => [$asociadoRequerido, 'integer', 'exists:asociados,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'modo_pago_id' => ['required', 'integer', 'exists:modos_pago,id'],
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
            'tipo.required' => 'El tipo de movimiento es obligatorio.',
            'tipo.in' => 'El tipo de movimiento debe ser "ingreso", "egreso" o "inicial".',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto no puede ser negativo.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha debe ser una fecha válida.',
            'detalle.string' => 'La descripción debe ser un texto.',
            'detalle.max' => 'La descripción no puede tener más de 1000 caracteres.',
            'proyecto_id.integer' => 'El ID del proyecto debe ser un número entero.',
            'proyecto_id.exists' => 'El proyecto especificado no existe.',
            'asociado_id.integer' => 'El ID del asociado debe ser un número entero.',
            'asociado_id.exists' => 'El asociado especificado no existe.',
            'asociado_id.required' => 'El asociado es obligatorio para movimientos de tipo ingreso.',
            'proveedor_id.integer' => 'El ID del proveedor debe ser un número entero.',
            'proveedor_id.exists' => 'El proveedor especificado no existe.',
            'modo_pago_id.required' => 'El modo de pago es obligatorio.',
            'modo_pago_id.integer' => 'El ID del modo de pago debe ser un número
    entero.',
            'modo_pago_id.exists' => 'El modo de pago especificado no existe.',

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
