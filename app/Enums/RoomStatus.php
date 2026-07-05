<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RoomStatus: string implements HasColor, HasIcon, HasLabel
{
    case OUT_OF_SERVICE = 'out_of_service';
    case AVAILABLE = 'available';
    case CLEANING = 'cleaning';
    case RESERVED = 'reserved';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OUT_OF_SERVICE => 'Inhabilitada',
            self::AVAILABLE => 'Disponible',
            self::CLEANING => 'Limpieza',
            self::RESERVED => 'Reservada',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::OUT_OF_SERVICE => 'danger',
            self::AVAILABLE => 'success',
            self::CLEANING => 'warning',
            self::RESERVED => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::OUT_OF_SERVICE => 'heroicon-m-x-circle',
            self::AVAILABLE => 'heroicon-m-check-circle',
            self::CLEANING => 'heroicon-m-wrench-screwdriver',
            self::RESERVED => 'heroicon-m-bookmark',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }

    public static function array(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}
