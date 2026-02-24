<?php

namespace App\Http\Requests\PlanPago;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePlanPagoRequest extends FormRequest
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
            'asociado_ids' => ['required', 'array', 'min:1'],
            'asociado_ids.*' => ['required', 'integer', 'distinct', 'exists:asociados,id'],
            'descripcion' => ['required', 'string', 'max:255'],
            'cantidad_cuotas' => ['required', 'integer', 'min:1'],
            'fecha_primer_vencimiento' => ['required', 'date'],
            'frecuencia_mensual' => ['nullable', 'integer', 'min:1'],
            'importe_total' => ['nullable', 'numeric', 'min:0.01'],
            'importe_por_cuota' => ['nullable', 'numeric', 'min:0.01'],
            'cuotas' => ['nullable', 'array', 'min:1'],
            'cuotas.*.numero' => ['required_with:cuotas', 'integer', 'min:1', 'distinct'],
            'cuotas.*.fecha_vencimiento' => ['required_with:cuotas', 'date'],
            'cuotas.*.importe' => ['required_with:cuotas', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'asociado_ids.required' => 'Debes enviar al menos un asociado.',
            'asociado_ids.array' => 'Los asociados deben enviarse en un array.',
            'asociado_ids.min' => 'Debes enviar al menos un asociado.',
            'asociado_ids.*.exists' => 'Uno de los asociados especificados no existe.',
            'asociado_ids.*.distinct' => 'No se pueden repetir asociados en la solicitud.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'cantidad_cuotas.required' => 'La cantidad de cuotas es obligatoria.',
            'cantidad_cuotas.min' => 'La cantidad de cuotas debe ser al menos 1.',
            'fecha_primer_vencimiento.required' => 'La fecha del primer vencimiento es obligatoria.',
            'frecuencia_mensual.min' => 'La frecuencia mensual debe ser al menos 1.',
            'importe_total.min' => 'El importe total debe ser mayor a 0.',
            'importe_por_cuota.min' => 'El importe por cuota debe ser mayor a 0.',
            'cuotas.array' => 'Las cuotas deben enviarse en un array.',
            'cuotas.*.numero.distinct' => 'Los números de cuota no se pueden repetir.',
            'cuotas.*.importe.min' => 'El importe de cada cuota debe ser mayor a 0.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('asociado_id') && !$this->has('asociado_ids')) {
            $this->merge([
                'asociado_ids' => [(int) $this->input('asociado_id')],
            ]);
        }

        if (!$this->has('frecuencia_mensual')) {
            $this->merge(['frecuencia_mensual' => 1]);
        }
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $importeTotal = $this->input('importe_total');
            $importePorCuota = $this->input('importe_por_cuota');
            $cantidadCuotas = (int) $this->input('cantidad_cuotas', 0);
            $cuotas = $this->input('cuotas', []);

            if (is_null($importeTotal) && is_null($importePorCuota)) {
                $validator->errors()->add(
                    'importe_total',
                    'Debes enviar importe_total o importe_por_cuota.'
                );
            }

            if (!is_array($cuotas) || $cuotas === []) {
                return;
            }

            if ($cantidadCuotas > 0 && count($cuotas) !== $cantidadCuotas) {
                $validator->errors()->add(
                    'cuotas',
                    'La cantidad de cuotas enviadas debe coincidir con cantidad_cuotas.'
                );
            }

            $numeros = collect($cuotas)
                ->pluck('numero')
                ->filter(fn ($numero) => is_numeric($numero))
                ->map(fn ($numero) => (int) $numero)
                ->sort()
                ->values()
                ->all();

            if ($cantidadCuotas > 0) {
                $esperados = range(1, $cantidadCuotas);

                if ($numeros !== $esperados) {
                    $validator->errors()->add(
                        'cuotas',
                        'Los números de cuota deben ser correlativos desde 1 hasta cantidad_cuotas.'
                    );
                }
            }

            $primeraCuota = collect($cuotas)
                ->sortBy('numero')
                ->first();

            if (is_array($primeraCuota)
                && isset($primeraCuota['fecha_vencimiento'])
                && $this->filled('fecha_primer_vencimiento')
                && $primeraCuota['fecha_vencimiento'] !== $this->input('fecha_primer_vencimiento')
            ) {
                $validator->errors()->add(
                    'fecha_primer_vencimiento',
                    'La fecha_primer_vencimiento debe coincidir con la fecha de la cuota 1.'
                );
            }

            if (!is_null($importeTotal)) {
                $sumaCuotas = round((float) collect($cuotas)
                    ->sum(fn ($cuota) => (float) ($cuota['importe'] ?? 0)), 2);

                if (round((float) $importeTotal, 2) !== $sumaCuotas) {
                    $validator->errors()->add(
                        'cuotas',
                        'La suma de importes de cuotas debe coincidir con importe_total.'
                    );
                }
            }
        });
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
