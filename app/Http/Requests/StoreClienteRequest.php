<?php

namespace App\Http\Requests;

use App\Enums\ClienteEstado;
use App\Enums\Genero;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::SuperAdmin, UserRole::Empleado) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Account
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],

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
            'entrenador_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', [UserRole::SuperAdmin->value, UserRole::Empleado->value]))],
            'nutriologo_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', [UserRole::SuperAdmin->value, UserRole::Empleado->value]))],
            'notas' => ['nullable', 'string'],
        ];
    }
}
