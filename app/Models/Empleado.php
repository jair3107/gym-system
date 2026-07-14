<?php

namespace App\Models;

use App\Enums\EmpleadoEstado;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'telefono',
        'fecha_contratacion',
        'salario',
        'direccion',
        'foto',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_contratacion' => 'date',
        'salario' => 'decimal:2',
        'estado' => EmpleadoEstado::class,
    ];

    /**
     * The user account (login) that owns this employee profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getNombreAttribute(): ?string
    {
        return $this->user?->name;
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

    public function scopeEstado(Builder $query, ?string $estado): Builder
    {
        if (blank($estado)) {
            return $query;
        }

        return $query->where('estado', $estado);
    }
}
