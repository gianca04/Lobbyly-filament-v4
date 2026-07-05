<?php

namespace App\Filament\Resources\Features\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::fields());
    }

    /**
     * @return array<int, mixed>
     */
    public static function fields(): array
    {
        return [
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->unique(ignoreRecord: true),
            TextInput::make('price')
                ->label('Precio')
                ->numeric()
                ->prefix('S/')
                ->default(0.00)
                ->required(),
            Toggle::make('is_active')
                ->label('¿Está activa?')
                ->helperText('La característica será visible en el resto de formularios.')
                ->default(true),
            Toggle::make('is_removable')
                ->label('¿Es removible?')
                ->helperText('Al ser removible la característica podrá ser eliminada al momento del alquiler o reservación.')
                ->default(true),
        ];
    }
}
