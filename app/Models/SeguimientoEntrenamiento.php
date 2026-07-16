<?php

namespace App\Models;

use App\Enums\SeguimientoEstado;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeguimientoEntrenamiento extends Model
{
    use HasFactory;

    protected $table = 'seguimientos_entrenamiento';

    protected $fillable = [
        'entrenador_id',
        'cliente_id',
        'rutina_id',
        'fecha_inicio',
        'fecha_fin',
        'peso_inicial',
        'peso_meta',
        'masa_inicial',
        'masa_meta',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'peso_inicial' => 'decimal:2',
        'peso_meta' => 'decimal:2',
        'masa_inicial' => 'decimal:2',
        'masa_meta' => 'decimal:2',
        'estado' => SeguimientoEstado::class,
    ];

    /**
     * El entrenador (usuario) responsable del seguimiento.
     */
    public function entrenador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entrenador_id');
    }

    /**
     * El alumno (cliente) al que se le da seguimiento.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * La rutina asignada (puede quedar nula si se elimina).
     */
    public function rutina(): BelongsTo
    {
        return $this->belongsTo(Rutina::class);
    }

    /**
     * Mediciones registradas (progreso), de la más reciente a la más antigua.
     */
    public function mediciones(): HasMany
    {
        return $this->hasMany(MedicionEntrenamiento::class)->orderByDesc('fecha')->orderByDesc('id');
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
     * Masa muscular más reciente registrada; si no hay, la masa inicial.
     */
    public function getMasaActualAttribute(): ?string
    {
        return $this->mediciones()->orderByDesc('fecha')->orderByDesc('id')->value('masa_muscular')
            ?? $this->masa_inicial;
    }

    /**
     * Cambio de masa muscular (actual - inicial); null si falta alguno.
     */
    public function getCambioMasaAttribute(): ?float
    {
        $actual = $this->masa_actual;

        if ($this->masa_inicial === null || $actual === null) {
            return null;
        }

        return round((float) $actual - (float) $this->masa_inicial, 2);
    }

    /**
     * Scope: solo seguimientos de un entrenador.
     */
    public function scopeDeEntrenador(Builder $query, int $userId): Builder
    {
        return $query->where('entrenador_id', $userId);
    }
}
