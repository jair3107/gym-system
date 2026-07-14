<?php

namespace App\Enums;

enum ClienteEstado: string
{
    case Activo = 'activo';
    case Inactivo = 'inactivo';
    case Suspendido = 'suspendido';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Inactivo => 'Inactivo',
            self::Suspendido => 'Suspendido',
        };
    }

    /**
     * Tailwind badge classes for this estado.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Activo => 'bg-green-500/15 text-green-400 ring-1 ring-green-500/20',
            self::Inactivo => 'bg-zinc-500/15 text-zinc-400 ring-1 ring-zinc-500/20',
            self::Suspendido => 'bg-red-500/15 text-red-400 ring-1 ring-red-500/20',
        };
    }
}
