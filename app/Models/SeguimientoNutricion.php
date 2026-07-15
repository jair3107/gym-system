<?php

namespace App\Models;

use App\Enums\SeguimientoEstado;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeguimientoNutricion extends Model
{
    use HasFactory;

    protected $table = 'seguimientos_nutricion';

    protected $fillable = [
        'nutriologo_id',
        'cliente_id',
        'dieta_id',
        'fecha_inicio',
        'fecha_fin',
        'peso_inicial',
        'peso_meta',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'peso_inicial' => 'decimal:2',
        'peso_meta' => 'decimal:2',
        'estado' => SeguimientoEstado::class,
    ];

    /**
     * El nutriólogo (usuario) responsable del seguimiento.
     */
    public function nutriologo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nutriologo_id');
    }

    /**
     * El paciente (cliente) al que se le da seguimiento.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * La dieta asignada (puede quedar nula si se elimina).
     */
    public function dieta(): BelongsTo
    {
        return $this->belongsTo(Dieta::class);
    }

    /**
     * Mediciones de peso registradas (progreso), de la más reciente a la más antigua.
     */
    public function mediciones(): HasMany
    {
        return $this->hasMany(MedicionNutricion::class)->orderByDesc('fecha')->orderByDesc('id');
    }

    /**
     * Peso más reciente registrado; si no hay mediciones, el peso inicial.
     */
    public function getPesoActualAttribute(): ?string
    {
        return $this->mediciones()->orderByDesc('fecha')->orderByDesc('id')->value('peso')
            ?? $this->peso_inicial;
    }

    /**
     * Cambio de peso (actual - inicial); null si falta alguno.
     */
    public function getCambioPesoAttribute(): ?float
    {
        $actual = $this->peso_actual;

        if ($this->peso_inicial === null || $actual === null) {
            return null;
        }

        return round((float) $actual - (float) $this->peso_inicial, 2);
    }

    /**
     * Scope: solo seguimientos de un nutriólogo.
     */
    public function scopeDeNutriologo(Builder $query, int $userId): Builder
    {
        return $query->where('nutriologo_id', $userId);
    }
}
