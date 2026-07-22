<?php

namespace App\Http\Requests;

use App\Enums\MetodoPago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarVentas() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'auto_renovacion' => $this->boolean('auto_renovacion'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => ['nullable', Rule::exists('clientes', 'id')],
            'metodo_pago' => ['required', Rule::enum(MetodoPago::class)],
            'auto_renovacion' => ['boolean'],
            'notas' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.tipo' => ['required', Rule::in(['membresia', 'producto', 'servicio'])],
            'items.*.id' => ['required', 'integer'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Agrega al menos un concepto a la venta.',
            'items.min' => 'Agrega al menos un concepto a la venta.',
        ];
    }
}
