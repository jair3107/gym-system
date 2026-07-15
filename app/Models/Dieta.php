<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dieta extends Model
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
     * Tiempos de comida (filas de la tabla), en orden.
     *
     * @var array<string, string>
     */
    public const TIEMPOS = [
        'colacion_1' => '1ª Colación',
        'desayuno' => 'Desayuno',
        'colacion_2' => '2ª Colación',
        'comida' => 'Comida',
        'colacion_3' => '3ª Colación',
        'cena' => 'Cena',
    ];

    protected $fillable = [
        'nutriologo_id',
        'nombre',
        'objetivo',
        'calorias_objetivo',
        'descripcion',
        'activa',
        'plan',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'plan' => 'array',
    ];

    /**
     * El nutriólogo (usuario) dueño de esta dieta.
     */
    public function nutriologo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nutriologo_id');
    }

    /**
     * Horario configurado para un tiempo de comida (fila).
     */
    public function horario(string $tiempo): ?string
    {
        return $this->plan['horarios'][$tiempo] ?? null;
    }

    /**
     * Contenido (alimentos) para un día + tiempo de comida (celda).
     */
    public function contenido(string $dia, string $tiempo): ?string
    {
        return $this->plan['dias'][$dia][$tiempo] ?? null;
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
     * Scope: solo dietas de un nutriólogo (para que cada quien vea las suyas).
     */
    public function scopeDeNutriologo(Builder $query, int $userId): Builder
    {
        return $query->where('nutriologo_id', $userId);
    }
}
