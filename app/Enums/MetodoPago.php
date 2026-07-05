<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MetodoPago: string implements HasLabel
{
    case EFECTIVO = 'efectivo';
    case YAPE = 'yape';
    case PLIN = 'plin';
    case TARJETA = 'tarjeta';
    case TRANSFERENCIA = 'transferencia';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EFECTIVO => 'Efectivo',
            self::YAPE => 'Yape',
            self::PLIN => 'Plin',
            self::TARJETA => 'Tarjeta',
            self::TRANSFERENCIA => 'Transferencia',
        };
    }
}
