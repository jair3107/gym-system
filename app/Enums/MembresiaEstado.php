<?php

namespace App\Enums;

enum MembresiaEstado: string
{
    case Activa = 'activa';
    case Vencida = 'vencida';
    case Cancelada = 'cancelada';
    case Renovada = 'renovada';

    public function label(): string
    {
        return match ($this) {
            self::Activa => 'Vigente',
            self::Vencida => 'Vencida',
            self::Cancelada => 'Cancelada',
            self::Renovada => 'Renovada',
        };
    }

    /**
     * Tailwind badge classes for this estado de vigencia.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Activa => 'bg-green-500/15 text-green-400 ring-1 ring-green-500/20',
            self::Vencida => 'bg-zinc-500/15 text-zinc-400 ring-1 ring-zinc-500/20',
            self::Cancelada => 'bg-red-500/15 text-red-400 ring-1 ring-red-500/20',
            self::Renovada => 'bg-blue-500/15 text-blue-400 ring-1 ring-blue-500/20',
        };
    }
}
