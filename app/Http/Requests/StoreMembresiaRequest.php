<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembresiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarMembresias() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activa' => $this->boolean('activa'),
            'renovable' => $this->boolean('renovable'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'duracion_dias' => ['required', 'integer', 'min:1', 'max:3650'],
            'activa' => ['boolean'],
            'renovable' => ['boolean'],

            // Beneficios incluidos (productos y servicios del catálogo).
            'beneficio_productos' => ['nullable', 'array'],
            'beneficio_productos.*' => ['integer', 'exists:productos,id'],
            'beneficio_servicios' => ['nullable', 'array'],
            'beneficio_servicios.*' => ['integer', 'exists:servicios,id'],
        ];
    }
}
