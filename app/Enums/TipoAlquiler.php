<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoAlquiler: string implements HasLabel
{
    case HORAS = 'horas';
    case DIAS = 'dias';
    case INDEFINIDO = 'indefinido';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::HORAS => 'Horas',
            self::DIAS => 'Días',
            self::INDEFINIDO => 'Indefinido',
        };
    }
}
