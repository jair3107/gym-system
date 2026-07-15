<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isEmpleado(): bool
    {
        return $this->role === UserRole::Empleado;
    }

    public function isCliente(): bool
    {
        return $this->role === UserRole::Cliente;
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * The client profile linked to this user account (if it is a cliente).
     */
    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class);
    }

    /**
     * The employee profile linked to this user account (if it is an empleado).
     */
    public function empleado(): HasOne
    {
        return $this->hasOne(Empleado::class);
    }

    /**
     * Puestos (especialidades) asignados a este empleado.
     */
    public function puestos(): BelongsToMany
    {
        return $this->belongsToMany(Puesto::class, 'empleado_puesto');
    }

    /**
     * Clientes que tienen a este usuario como entrenador (sus alumnos).
     */
    public function alumnos(): HasMany
    {
        return $this->hasMany(Cliente::class, 'entrenador_id');
    }

    /**
     * Clientes que tienen a este usuario como nutriólogo (sus pacientes).
     */
    public function pacientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'nutriologo_id');
    }

    /**
     * ¿El empleado tiene alguno de estos puestos (por slug)?
     */
    public function hasPuesto(string ...$slugs): bool
    {
        return $this->puestos->whereIn('slug', $slugs)->isNotEmpty();
    }

    /**
     * ¿Puede gestionar empleados? El dueño (superadmin) o un empleado con
     * puesto "admin".
     */
    public function puedeGestionarEmpleados(): bool
    {
        return $this->isSuperAdmin() || $this->hasPuesto(Puesto::ADMIN);
    }

    /**
     * ¿Puede gestionar el módulo de nutrición (dietas)? El dueño (superadmin)
     * o un empleado con puesto "nutriologo".
     */
    public function puedeGestionarNutricion(): bool
    {
        return $this->isSuperAdmin() || $this->hasPuesto(Puesto::NUTRIOLOGO);
    }

    /**
     * Dietas creadas por este usuario (como nutriólogo).
     */
    public function dietas(): HasMany
    {
        return $this->hasMany(Dieta::class, 'nutriologo_id');
    }

    /**
     * Scope: only users with the empleado (or superadmin) role — those who can
     * be assigned as entrenador or nutriólogo.
     */
    public function scopeStaff(Builder $query): Builder
    {
        return $query->whereIn('role', [UserRole::SuperAdmin->value, UserRole::Empleado->value]);
    }

    /**
     * Scope: usuarios que tienen el puesto indicado (por slug).
     */
    public function scopeConPuesto(Builder $query, string $slug): Builder
    {
        return $query->whereHas('puestos', fn (Builder $q) => $q->where('slug', $slug));
    }
}
