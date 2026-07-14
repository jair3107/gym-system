<?php

namespace App\Enums;

enum EmpleadoEstado: string
{
    case Activo = 'activo';
    case Inactivo = 'inactivo';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Inactivo => 'Inactivo',
        };
    }

    /**
     * Tailwind badge classes for this estado (dark theme).
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Activo => 'bg-green-500/15 text-green-400 ring-1 ring-green-500/20',
            self::Inactivo => 'bg-zinc-500/15 text-zinc-400 ring-1 ring-zinc-500/20',
        };
    }
}
