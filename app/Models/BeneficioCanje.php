<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BeneficioCanje extends Model
{
    use HasFactory;

    protected $table = 'beneficio_canjes';

    protected $fillable = [
        'membresia_cliente_id',
        'beneficiable_type',
        'beneficiable_id',
        'descripcion',
        'cantidad',
        'atendido_por',
        'nota',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    /**
     * Suscripción (vigencia) que respalda este canje.
     */
    public function membresiaCliente(): BelongsTo
    {
        return $this->belongsTo(MembresiaCliente::class);
    }

    /**
     * Beneficio entregado (producto o servicio).
     */
    public function beneficiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Usuario (staff) que registró la entrega.
     */
    public function atendidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }
}
