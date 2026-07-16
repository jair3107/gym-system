<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicionEntrenamientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarEntrenamiento() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'peso' => ['required', 'numeric', 'between:20,400'],
            'masa_muscular' => ['nullable', 'numeric', 'between:0,200'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}
