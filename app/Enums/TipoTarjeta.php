<?php

namespace App\Enums;

enum TipoTarjeta: string
{
    case Credito = 'credito';
    case Debito = 'debito';

    public function label(): string
    {
        return match ($this) {
            self::Credito => 'Crédito',
            self::Debito => 'Débito',
        };
    }
}
