<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rutina extends Model
{
    use HasFactory;

    /**
     * Días de la semana (columnas de la tabla), en orden.
     *
     * @var array<string, string>
     */
    public const DIAS = [
        'lunes' => 'Lunes',
        'martes' => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves' => 'Jueves',
        'viernes' => 'Viernes',
        'sabado' => 'Sábado',
        'domingo' => 'Domingo',
    ];

    /**
     * Bloques de la sesión (filas de la tabla), en orden.
     *
     * @var array<string, string>
     */
    public const BLOQUES = [
        'calentamiento' => 'Calentamiento',
        'principal' => 'Ejercicio principal',
        'accesorios' => 'Accesorios',
        'cardio' => 'Cardio',
        'enfriamiento' => 'Enfriamiento',
    ];

    protected $fillable = [
        'entrenador_id',
        'nombre',
        'objetivo',
        'nivel',
        'duracion_min',
        'descripcion',
        'activa',
        'plan',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'plan' => 'array',
    ];

    /**
     * El entrenador (usuario) dueño de esta rutina.
     */
    public function entrenador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entrenador_id');
    }

    /**
     * Duración configurada para un bloque de la sesión (fila).
     */
    public function duracion(string $bloque): ?string
    {
        return $this->plan['duraciones'][$bloque] ?? null;
    }

    /**
     * Contenido (ejercicios) para un día + bloque (celda).
     */
    public function contenido(string $dia, string $bloque): ?string
    {
        return $this->plan['dias'][$dia][$bloque] ?? null;
    }

    /**
     * Scope: filtrar por nombre u objetivo.
     */
    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        if (blank($termino)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
                ->orWhere('objetivo', 'like', "%{$termino}%");
        });
    }

    /**
     * Scope: solo rutinas de un entrenador (para que cada quien vea las suyas).
     */
    public function scopeDeEntrenador(Builder $query, int $userId): Builder
    {
        return $query->where('entrenador_id', $userId);
    }
}
