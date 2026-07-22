<?php

namespace App\Http\Requests;

use App\Enums\TipoTarjeta;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTarjetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarVentas() ?? false;
    }

    protected function prepareForValidation(): void
    {
        // Deja solo dígitos del número (no se almacena, solo se valida).
        $this->merge([
            'numero' => preg_replace('/\D/', '', (string) $this->input('numero')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titular' => ['required', 'string', 'max:255'],
            'numero' => ['required', 'digits_between:13,19'],
            'tipo' => ['required', Rule::enum(TipoTarjeta::class)],
            'mes_exp' => ['required', 'integer', 'between:1,12'],
            'anio_exp' => ['required', 'integer', 'between:2000,2100'],
            // El CVV puede enviarse pero NO se almacena.
            'cvv' => ['nullable', 'digits_between:3,4'],
        ];
    }

    /**
     * Validaciones extra: Luhn y que no esté vencida.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $numero = (string) $this->input('numero');

            if ($numero !== '' && ! $this->pasaLuhn($numero)) {
                $validator->errors()->add('numero', 'El número de tarjeta no es válido.');
            }

            $mes = (int) $this->input('mes_exp');
            $anio = (int) $this->input('anio_exp');
            if ($mes >= 1 && $mes <= 12 && $anio >= 2000) {
                $vencimiento = now()->setDate($anio, $mes, 1)->endOfMonth();
                if ($vencimiento->isPast()) {
                    $validator->errors()->add('anio_exp', 'La tarjeta está vencida.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'numero.digits_between' => 'El número de tarjeta debe tener entre 13 y 19 dígitos.',
        ];
    }

    /**
     * Algoritmo de Luhn para validar el número de tarjeta.
     */
    protected function pasaLuhn(string $numero): bool
    {
        $suma = 0;
        $alternar = false;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $digito = (int) $numero[$i];

            if ($alternar) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }

            $suma += $digito;
            $alternar = ! $alternar;
        }

        return $suma % 10 === 0;
    }
}
