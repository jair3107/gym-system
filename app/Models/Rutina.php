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
     * Enfoque muscular del día (p. ej. "Pecho - Tríceps").
     */
    public function enfoque(string $dia): ?string
    {
        return $this->plan['dias'][$dia]['enfoque'] ?? null;
    }

    /**
     * Ejercicios de un día: lista de ['nombre', 'series', 'apuntes'].
     *
     * @return array<int, array<string, string>>
     */
    public function ejercicios(string $dia): array
    {
        return $this->plan['dias'][$dia]['ejercicios'] ?? [];
    }

    /**
     * ¿El día tiene contenido (enfoque o ejercicios)?
     */
    public function tieneDia(string $dia): bool
    {
        return filled($this->enfoque($dia)) || $this->ejercicios($dia) !== [];
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
