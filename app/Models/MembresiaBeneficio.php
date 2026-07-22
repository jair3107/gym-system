<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MembresiaBeneficio extends Model
{
    use HasFactory;

    protected $table = 'membresia_beneficio';

    protected $fillable = [
        'membresia_id',
        'beneficiable_type',
        'beneficiable_id',
    ];

    /**
     * Plan al que pertenece este beneficio.
     */
    public function membresia(): BelongsTo
    {
        return $this->belongsTo(Membresia::class);
    }

    /**
     * Producto o servicio incluido.
     */
    public function beneficiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Etiqueta del tipo de beneficio.
     */
    public function tipoLabel(): string
    {
        return $this->beneficiable_type === 'producto' ? 'Producto' : 'Servicio';
    }
}
