<?php

namespace App\Models;

use App\Enums\MembresiaEstado;
use App\Enums\MetodoPago;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembresiaCliente extends Model
{
    use HasFactory;

    protected $table = 'membresias_cliente';

    protected $fillable = [
        'cliente_id',
        'membresia_id',
        'venta_id',
        'cajero_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'auto_renovacion',
        'metodo_pago',
        'tarjeta_id',
        'recordatorio_enviado_at',
        'renovacion_de_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado' => MembresiaEstado::class,
        'auto_renovacion' => 'boolean',
        'metodo_pago' => MetodoPago::class,
        'recordatorio_enviado_at' => 'datetime',
    ];

    /**
     * Cliente titular de la membresía.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Plan de membresía contratado.
     */
    public function membresia(): BelongsTo
    {
        return $this->belongsTo(Membresia::class);
    }

    /**
     * Venta que originó esta membresía.
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * Cajero (o staff) responsable de la suscripción.
     */
    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }

    /**
     * Tarjeta con la que se hace el autocobro de la renovación.
     */
    public function tarjeta(): BelongsTo
    {
        return $this->belongsTo(Tarjeta::class);
    }

    /**
     * Periodo anterior del que proviene esta renovación.
     */
    public function renovacionDe(): BelongsTo
    {
        return $this->belongsTo(MembresiaCliente::class, 'renovacion_de_id');
    }

    /**
     * Entregas de beneficios registradas contra esta suscripción.
     */
    public function canjes(): HasMany
    {
        return $this->hasMany(BeneficioCanje::class);
    }

    /**
     * Beneficios incluidos (a través del plan).
     */
    public function beneficios()
    {
        return $this->membresia?->beneficios ?? collect();
    }

    /**
     * ¿Está vigente hoy? (activa y sin vencer).
     */
    public function estaVigente(): bool
    {
        return $this->estado === MembresiaEstado::Activa
            && $this->fecha_fin->endOfDay()->isFuture();
    }

    /**
     * Días restantes de vigencia (0 si ya venció).
     */
    public function getDiasRestantesAttribute(): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($this->fecha_fin->startOfDay(), false));
    }

    /**
     * Scope: solo membresías marcadas como activas.
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', MembresiaEstado::Activa->value);
    }
}
