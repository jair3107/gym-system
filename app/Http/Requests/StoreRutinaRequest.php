<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRutinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarEntrenamiento() ?? false;
    }

    /**
     * Normaliza el checkbox "activa" antes de validar.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'activa' => $this->boolean('activa'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'objetivo' => ['nullable', 'string', 'max:255'],
            'nivel' => ['nullable', 'string', 'max:255'],
            'duracion_min' => ['nullable', 'integer', 'min:0', 'max:600'],
            'descripcion' => ['nullable', 'string'],
            'activa' => ['boolean'],

            // Plan semanal: por día un enfoque y una lista de ejercicios.
            'plan' => ['nullable', 'array'],
            'plan.dias' => ['nullable', 'array'],
            'plan.dias.*' => ['nullable', 'array'],
            'plan.dias.*.enfoque' => ['nullable', 'string', 'max:255'],
            'plan.dias.*.ejercicios' => ['nullable', 'array'],
            'plan.dias.*.ejercicios.*' => ['nullable', 'array'],
            'plan.dias.*.ejercicios.*.nombre' => ['nullable', 'string', 'max:255'],
            'plan.dias.*.ejercicios.*.series' => ['nullable', 'string', 'max:100'],
            'plan.dias.*.ejercicios.*.apuntes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
