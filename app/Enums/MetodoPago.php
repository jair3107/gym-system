<?php

namespace App\Enums;

enum MetodoPago: string
{
    case Efectivo = 'efectivo';
    case Tarjeta = 'tarjeta';
    case Transferencia = 'transferencia';

    public function label(): string
    {
        return match ($this) {
            self::Efectivo => 'Efectivo',
            self::Tarjeta => 'Tarjeta',
            self::Transferencia => 'Transferencia',
        };
    }

    /**
     * Tailwind badge classes for this método de pago.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Efectivo => 'bg-green-500/15 text-green-400 ring-1 ring-green-500/20',
            self::Tarjeta => 'bg-blue-500/15 text-blue-400 ring-1 ring-blue-500/20',
            self::Transferencia => 'bg-purple-500/15 text-purple-400 ring-1 ring-purple-500/20',
        };
    }
}
