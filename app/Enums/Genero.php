<?php

namespace App\Enums;

enum Genero: string
{
    case Masculino = 'masculino';
    case Femenino = 'femenino';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Masculino => 'Masculino',
            self::Femenino => 'Femenino',
            self::Otro => 'Otro',
        };
    }
}
