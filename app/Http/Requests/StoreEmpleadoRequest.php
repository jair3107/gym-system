<?php

namespace App\Http\Requests;

use App\Enums\EmpleadoEstado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreEmpleadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarEmpleados() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Cuenta
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],

            // Perfil
            'telefono' => ['nullable', 'string', 'max:30'],
            'fecha_contratacion' => ['required', 'date'],
            'salario' => ['nullable', 'numeric', 'min:0'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'estado' => ['required', Rule::enum(EmpleadoEstado::class)],
            'notas' => ['nullable', 'string'],

            // Puestos (muchos a muchos)
            'puestos' => ['required', 'array', 'min:1'],
            'puestos.*' => ['integer', Rule::exists('puestos', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'puestos.required' => 'Selecciona al menos un puesto.',
            'puestos.min' => 'Selecciona al menos un puesto.',
        ];
    }
}
