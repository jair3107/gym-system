<?php

namespace App\Http\Requests;

use App\Enums\SeguimientoEstado;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeguimientoEntrenamientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarEntrenamiento() ?? false;
    }

    /**
     * El alumno no se cambia al editar; el resto sí.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'rutina_id' => [
                'nullable',
                Rule::exists('rutinas', 'id')->where(function (Builder $q) use ($user) {
                    return $user->isSuperAdmin() ? $q : $q->where('entrenador_id', $user->id);
                }),
            ],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'peso_inicial' => ['nullable', 'numeric', 'between:20,400'],
            'peso_meta' => ['nullable', 'numeric', 'between:20,400'],
            'masa_inicial' => ['nullable', 'numeric', 'between:0,200'],
            'masa_meta' => ['nullable', 'numeric', 'between:0,200'],
            'estado' => ['required', Rule::enum(SeguimientoEstado::class)],
            'notas' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'rutina_id.exists' => 'La rutina seleccionada no te pertenece.',
        ];
    }
}
