<?php

namespace App\Enum;

enum LatoArea: string
{
    case UNICA = 'unica';
    case SINISTRA = 'sinistra';
    case DESTRA = 'destra';

    public function label(): string
    {
        return match ($this) {
            self::UNICA => 'Fila unica',
            self::SINISTRA => 'Lato sinistro',
            self::DESTRA => 'Lato destro',
        };
    }

    public function labelBreve(): string
    {
        return match ($this) {
            self::UNICA => 'Unica',
            self::SINISTRA => 'Sx',
            self::DESTRA => 'Dx',
        };
    }
}
