<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicionNutricionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarNutricion() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'peso' => ['required', 'numeric', 'between:20,400'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}
