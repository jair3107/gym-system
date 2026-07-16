<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicionEntrenamiento extends Model
{
    use HasFactory;

    protected $table = 'mediciones_entrenamiento';

    protected $fillable = [
        'seguimiento_entrenamiento_id',
        'fecha',
        'peso',
        'masa_muscular',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'peso' => 'decimal:2',
        'masa_muscular' => 'decimal:2',
    ];

    /**
     * El seguimiento al que pertenece esta medición.
     */
    public function seguimiento(): BelongsTo
    {
        return $this->belongsTo(SeguimientoEntrenamiento::class, 'seguimiento_entrenamiento_id');
    }
}
