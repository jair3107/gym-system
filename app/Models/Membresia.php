<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membresia extends Model
{
    use HasFactory;

    protected $table = 'membresias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'duracion_dias',
        'activa',
        'renovable',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'duracion_dias' => 'integer',
        'activa' => 'boolean',
        'renovable' => 'boolean',
    ];

    /**
     * Beneficios (productos/servicios) incluidos en el plan.
     */
    public function beneficios(): HasMany
    {
        return $this->hasMany(MembresiaBeneficio::class);
    }

    /**
     * Scope: filtrar por nombre o descripción.
     */
    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        if (blank($termino)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
                ->orWhere('descripcion', 'like', "%{$termino}%");
        });
    }

    /**
     * Resumen de vigencia legible (ej. "30 días").
     */
    public function getVigenciaTextoAttribute(): string
    {
        return $this->duracion_dias.' '.($this->duracion_dias === 1 ? 'día' : 'días');
    }
}
