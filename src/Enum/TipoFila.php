<?php

namespace App\Enum;

enum TipoFila: string
{
    case UNICA = 'unica';
    case DIVISA = 'divisa';

    public function label(): string
    {
        return match ($this) {
            self::UNICA => 'Fila unica',
            self::DIVISA => 'Fila divisa (sinistra/destra)',
        };
    }
}
