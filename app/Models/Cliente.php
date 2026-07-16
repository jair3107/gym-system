<?php

namespace App\Models;

use App\Enums\ClienteEstado;
use App\Enums\Genero;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'telefono',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'foto',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'fecha_ingreso',
        'estado',
        'entrenador_id',
        'nutriologo_id',
        'notas',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'estado' => ClienteEstado::class,
        'genero' => Genero::class,
    ];

    /**
     * The user account (login) that owns this client profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The empleado assigned as this client's personal trainer.
     */
    public function entrenador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entrenador_id');
    }

    /**
     * The empleado assigned as this client's nutritionist.
     */
    public function nutriologo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nutriologo_id');
    }

    /**
     * Seguimientos de entrenamiento de este cliente (como alumno).
     */
    public function seguimientosEntrenamiento(): HasMany
    {
        return $this->hasMany(SeguimientoEntrenamiento::class);
    }

    /**
     * Seguimientos de nutrición de este cliente (como paciente).
     */
    public function seguimientosNutricion(): HasMany
    {
        return $this->hasMany(SeguimientoNutricion::class);
    }

    /**
     * Client's display name, delegated to the user account.
     */
    public function getNombreAttribute(): ?string
    {
        return $this->user?->name;
    }

    /**
     * Client's age in years, derived from fecha_nacimiento.
     */
    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nacimiento?->age;
    }

    /**
     * Scope: filter by name or email of the linked user account.
     */
    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        if (blank($termino)) {
            return $query;
        }

        return $query->whereHas('user', function (Builder $q) use ($termino) {
            $q->where('name', 'like', "%{$termino}%")
                ->orWhere('email', 'like', "%{$termino}%");
        });
    }

    /**
     * Scope: filter by estado.
     */
    public function scopeEstado(Builder $query, ?string $estado): Builder
    {
        if (blank($estado)) {
            return $query;
        }

        return $query->where('estado', $estado);
    }
}
