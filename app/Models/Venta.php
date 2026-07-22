<?php

namespace App\Models;

use App\Enums\MetodoPago;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'cajero_id',
        'cliente_id',
        'total',
        'metodo_pago',
        'automatica',
        'notas',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'metodo_pago' => MetodoPago::class,
        'automatica' => 'boolean',
    ];

    /**
     * Cajero (usuario) que registró la venta.
     */
    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }

    /**
     * Cliente al que se le vendió (puede ser null en venta de mostrador).
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Líneas (conceptos) de la venta.
     */
    public function items(): HasMany
    {
        return $this->hasMany(VentaItem::class);
    }

    /**
     * Vigencias de membresía que originó esta venta.
     */
    public function membresiasCliente(): HasMany
    {
        return $this->hasMany(MembresiaCliente::class);
    }

    /**
     * Scope: ventas registradas por un cajero.
     */
    public function scopeDeCajero(Builder $query, int $cajeroId): Builder
    {
        return $query->where('cajero_id', $cajeroId);
    }
}
