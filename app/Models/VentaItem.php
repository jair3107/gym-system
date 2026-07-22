<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VentaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'vendible_type',
        'vendible_id',
        'descripcion',
        'precio_unitario',
        'cantidad',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'cantidad' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Venta a la que pertenece esta línea.
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * Elemento vendido (membresía, producto o servicio).
     */
    public function vendible(): MorphTo
    {
        return $this->morphTo();
    }
}
