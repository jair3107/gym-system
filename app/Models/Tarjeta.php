<?php

namespace App\Models;

use App\Enums\TipoTarjeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarjeta extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'titular',
        'marca',
        'tipo',
        'ultimos4',
        'mes_exp',
        'anio_exp',
        'primaria',
    ];

    protected $casts = [
        'tipo' => TipoTarjeta::class,
        'mes_exp' => 'integer',
        'anio_exp' => 'integer',
        'primaria' => 'boolean',
    ];

    /**
     * Cliente dueño de la tarjeta.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Número enmascarado para mostrar (nunca se guarda el completo).
     */
    public function getMascaraAttribute(): string
    {
        return '•••• '.$this->ultimos4;
    }

    /**
     * Vencimiento en formato MM/AA.
     */
    public function getVenceAttribute(): string
    {
        return str_pad((string) $this->mes_exp, 2, '0', STR_PAD_LEFT).'/'.substr((string) $this->anio_exp, -2);
    }

    /**
     * Nombre legible de la marca.
     */
    public function getMarcaLabelAttribute(): string
    {
        return match ($this->marca) {
            'visa' => 'Visa',
            'mastercard' => 'Mastercard',
            'amex' => 'American Express',
            'discover' => 'Discover',
            default => 'Tarjeta',
        };
    }

    /**
     * ¿Ya venció? (fin del mes de expiración).
     */
    public function estaVencida(): bool
    {
        return now()->startOfMonth()->gt(
            now()->setDate($this->anio_exp, $this->mes_exp, 1)->startOfMonth(),
        );
    }

    /**
     * Deriva la marca a partir del número (solo por prefijo, sin guardarlo).
     */
    public static function marcaDesde(string $numero): string
    {
        $numero = preg_replace('/\D/', '', $numero);

        return match (true) {
            str_starts_with($numero, '4') => 'visa',
            (bool) preg_match('/^3[47]/', $numero) => 'amex',
            (bool) preg_match('/^5[1-5]/', $numero) => 'mastercard',
            str_starts_with($numero, '6') => 'discover',
            default => 'otra',
        };
    }

    /**
     * Scope: solo la(s) primaria(s).
     */
    public function scopePrimaria(Builder $query): Builder
    {
        return $query->where('primaria', true);
    }
}
