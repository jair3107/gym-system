<?php

namespace App\Http\Requests;

use App\Enums\SeguimientoEstado;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeguimientoNutricionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarNutricion() ?? false;
    }

    /**
     * El paciente no se cambia al editar; el resto sí.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
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
            'dieta_id.exists' => 'La dieta seleccionada no te pertenece.',
        ];
    }
}
