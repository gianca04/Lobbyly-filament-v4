<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoDocumento: string implements HasLabel
{
    case DNI = 'dni';
    case RUC = 'ruc';
    case CARNET_EXTRANJERIA = 'carnet_extranjeria';
    case PASAPORTE = 'pasaporte';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DNI => 'DNI',
            self::RUC => 'RUC',
            self::CARNET_EXTRANJERIA => 'Carné de Extranjería',
            self::PASAPORTE => 'Pasaporte',
        };
    }
}
