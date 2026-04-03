<?php

namespace App\Enums;

enum DocumentType: string
{
    case DNI = 'dni';
    case CE = 'ce';
    case PASAPORTE = 'pasaporte';
    case RUC = 'ruc';

    public function label(): string
    {
        return match ($this) {
            self::DNI => 'DNI',
            self::CE => 'Carnet de Extranjería',
            self::PASAPORTE => 'Pasaporte',
            self::RUC => 'RUC (Persona Jurídica)',
        };
    }

    public static function array(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}
