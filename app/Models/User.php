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
     * ¿Puede dar de alta / editar / eliminar clientes? El dueño (superadmin) o
     * un empleado con puesto "admin" o "cajero" (recepción).
     */
    public function puedeGestionarClientes(): bool
    {
        return $this->isSuperAdmin() || $this->hasPuesto(Puesto::ADMIN, Puesto::CAJERO);
    }

    /**
     * Cómo se llama la sección de clientes para este usuario: "Alumnos" para el
     * entrenador, "Pacientes" para el nutriólogo, "Clientes" para el resto.
     */
    public function etiquetaClientes(): string
    {
        if ($this->puedeGestionarClientes()) {
            return 'Clientes';
        }

        $entrena = $this->hasPuesto(Puesto::ENTRENADOR);
        $nutre = $this->hasPuesto(Puesto::NUTRIOLOGO);

        if ($entrena && ! $nutre) {
            return 'Alumnos';
        }

        if ($nutre && ! $entrena) {
            return 'Pacientes';
        }

        return 'Clientes';
    }

    /**
     * Texto de rol a mostrar en el panel: el/los puesto(s) reales del empleado,
     * "Super Administrador" para el dueño, y null para el cliente (no se muestra).
     */
    public function rolParaMostrar(): ?string
    {
        if ($this->isSuperAdmin()) {
            return 'Super Administrador';
        }

        if ($this->isCliente()) {
            return null;
        }

        $nombres = $this->puestos->pluck('nombre')->all();

        return $nombres === [] ? 'Empleado' : implode(', ', $nombres);
    }

    /**
     * ¿Puede ver la ficha de este cliente? Los gestores ven a todos; el
     * entrenador/nutriólogo ve a los suyos y a los que aún no tienen asignado.
     */
    public function puedeVerCliente(Cliente $cliente): bool
    {
        if ($this->puedeGestionarClientes()) {
            return true;
        }

        if ($this->hasPuesto(Puesto::ENTRENADOR)
            && ($cliente->entrenador_id === $this->id || $cliente->entrenador_id === null)) {
            return true;
        }

        if ($this->hasPuesto(Puesto::NUTRIOLOGO)
            && ($cliente->nutriologo_id === $this->id || $cliente->nutriologo_id === null)) {
            return true;
        }

        return false;
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
     * ¿Puede gestionar el módulo de entrenamiento (rutinas)? El dueño (superadmin)
     * o un empleado con puesto "entrenador".
     */
    public function puedeGestionarEntrenamiento(): bool
    {
        return $this->isSuperAdmin() || $this->hasPuesto(Puesto::ENTRENADOR);
    }

    /**
     * ¿Puede gestionar el módulo de caja/ventas (productos, servicios y
     * registro de ventas)? El dueño (superadmin) o un empleado con puesto
     * "cajero".
     */
    public function puedeGestionarVentas(): bool
    {
        return $this->isSuperAdmin() || $this->hasPuesto(Puesto::CAJERO);
    }

    /**
     * ¿Puede gestionar el catálogo de planes de membresía? El dueño
     * (superadmin), un empleado con puesto "admin" o con puesto "cajero".
     */
    public function puedeGestionarMembresias(): bool
    {
        return $this->isSuperAdmin() || $this->hasPuesto(Puesto::ADMIN, Puesto::CAJERO);
    }

    /**
     * Ventas registradas por este usuario (como cajero).
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'cajero_id');
    }

    /**
     * Dietas creadas por este usuario (como nutriólogo).
     */
    public function dietas(): HasMany
    {
        return $this->hasMany(Dieta::class, 'nutriologo_id');
    }

    /**
     * Rutinas creadas por este usuario (como entrenador).
     */
    public function rutinas(): HasMany
    {
        return $this->hasMany(Rutina::class, 'entrenador_id');
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
