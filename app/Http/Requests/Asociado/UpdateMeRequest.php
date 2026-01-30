<?php

namespace App\Http\Requests\Asociado;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['nullable', 'string', 'min:2', 'max:80', 'required_without:organizacion_nombre'],
            'organizacion_nombre' => ['nullable', 'string', 'min:2', 'max:255', 'required_without:nombre'],
        ];
    }
}
