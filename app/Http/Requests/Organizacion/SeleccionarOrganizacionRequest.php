<?php

namespace App\Http\Requests\Organizacion;

use Illuminate\Foundation\Http\FormRequest;

class SeleccionarOrganizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organizacion_id' => ['required', 'integer', 'min:1'],
        ];
    }
}
