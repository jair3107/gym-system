<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicionNutricion extends Model
{
    use HasFactory;

    protected $table = 'mediciones_nutricion';

    protected $fillable = [
        'seguimiento_nutricion_id',
        'fecha',
        'peso',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'peso' => 'decimal:2',
    ];

    /**
     * El seguimiento al que pertenece esta medición.
     */
    public function seguimiento(): BelongsTo
    {
        return $this->belongsTo(SeguimientoNutricion::class, 'seguimiento_nutricion_id');
    }
}
