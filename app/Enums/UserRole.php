<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'superadmin';
    case Empleado = 'empleado';
    case Cliente = 'cliente';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrador',
            self::Empleado => 'Empleado',
            self::Cliente => 'Cliente',
        };
    }

    public function dashboard(): string
    {
        return match ($this) {
            self::SuperAdmin => 'admin.dashboard',
            self::Empleado => 'empleado.dashboard',
            self::Cliente => 'cliente.dashboard',
        };
    }
}
