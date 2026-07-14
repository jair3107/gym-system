<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Puesto extends Model
{
    use HasFactory;

    /** Slugs de puestos con lógica especial en la aplicación. */
    public const ADMIN = 'admin';
    public const CAJERO = 'cajero';
    public const NUTRIOLOGO = 'nutriologo';
    public const ENTRENADOR = 'entrenador';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
    ];

    /**
     * Empleados (usuarios) que tienen este puesto.
     */
    public function empleados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'empleado_puesto');
    }
}
