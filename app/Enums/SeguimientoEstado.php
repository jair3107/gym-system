<?php

namespace App\Enums;

enum SeguimientoEstado: string
{
    case Activo = 'activo';
    case Finalizado = 'finalizado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Finalizado => 'Finalizado',
            self::Cancelado => 'Cancelado',
        };
    }

    /**
     * Tailwind badge classes for this estado.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Activo => 'bg-green-500/15 text-green-400 ring-1 ring-green-500/20',
            self::Finalizado => 'bg-blue-500/15 text-blue-400 ring-1 ring-blue-500/20',
            self::Cancelado => 'bg-red-500/15 text-red-400 ring-1 ring-red-500/20',
        };
    }
}
