<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePuestoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarEmpleados() ?? false;
    }

    protected function prepareForValidation(): void
    {
        // El slug se deriva del nombre si no se envía.
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('nombre', '')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('puestos', 'slug')],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
