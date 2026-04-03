<?php

namespace App\Enums;

enum MovementType: string
{
    case INPUT = 'input';
    case OUTPUT = 'output';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::INPUT => 'Ingreso',
            self::OUTPUT => 'Salida',
            self::TRANSFER => 'Intercambio',
            self::ADJUSTMENT => 'Ajuste',
        };
    }

    public static function array(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}
