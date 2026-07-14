<?php

namespace App\Http\Requests;

use App\Enums\ClienteEstado;
use App\Enums\Genero;
use App\Enums\UserRole;
use App\Models\Puesto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::SuperAdmin, UserRole::Empleado) ?? false;
    }

    /**
     * Regla: el usuario asignado debe tener el puesto indicado (por slug).
     */
    protected function tienePuestoRule(string $slug)
    {
        $puestoId = Puesto::where('slug', $slug)->value('id');

        return Rule::exists('empleado_puesto', 'user_id')->where('puesto_id', $puestoId);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('cliente')->user_id;

        return [
            // Account
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'confirmed', Password::defaults()],

            // Profile
            'telefono' => ['nullable', 'string', 'max:30'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'genero' => ['nullable', Rule::enum(Genero::class)],
            'direccion' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'contacto_emergencia_nombre' => ['nullable', 'string', 'max:255'],
            'contacto_emergencia_telefono' => ['nullable', 'string', 'max:30'],
            'fecha_ingreso' => ['required', 'date'],
            'estado' => ['required', Rule::enum(ClienteEstado::class)],
            'entrenador_id' => ['nullable', $this->tienePuestoRule(Puesto::ENTRENADOR)],
            'nutriologo_id' => ['nullable', $this->tienePuestoRule(Puesto::NUTRIOLOGO)],
            'notas' => ['nullable', 'string'],
        ];
    }
}
