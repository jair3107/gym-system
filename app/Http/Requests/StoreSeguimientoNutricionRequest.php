<?php

namespace App\Http\Requests;

use App\Enums\SeguimientoEstado;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeguimientoNutricionRequest extends FormRequest
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
        $user = $this->user();

        return [
            // El paciente debe ser del nutriólogo (o cualquiera asignado, si es el dueño).
            'cliente_id' => [
                'required',
                Rule::exists('clientes', 'id')->where(function (Builder $q) use ($user) {
                    return $user->isSuperAdmin()
                        ? $q->whereNotNull('nutriologo_id')
                        : $q->where('nutriologo_id', $user->id);
                }),
            ],
            // La dieta debe ser del nutriólogo (o cualquiera, si es el dueño).
            'dieta_id' => [
                'nullable',
                Rule::exists('dietas', 'id')->where(function (Builder $q) use ($user) {
                    return $user->isSuperAdmin() ? $q : $q->where('nutriologo_id', $user->id);
                }),
            ],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'peso_inicial' => ['nullable', 'numeric', 'between:20,400'],
            'peso_meta' => ['nullable', 'numeric', 'between:20,400'],
            'estado' => ['required', Rule::enum(SeguimientoEstado::class)],
            'notas' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'El paciente seleccionado no está asignado a ti.',
            'dieta_id.exists' => 'La dieta seleccionada no te pertenece.',
        ];
    }
}
