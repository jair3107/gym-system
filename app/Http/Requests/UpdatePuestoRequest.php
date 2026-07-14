<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdatePuestoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarEmpleados() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('nombre', '')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $puestoId = $this->route('puesto')->id;

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('puestos', 'slug')->ignore($puestoId)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
