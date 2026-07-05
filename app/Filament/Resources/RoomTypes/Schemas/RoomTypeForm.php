<?php

namespace App\Filament\Resources\RoomTypes\Schemas;

use App\Filament\Resources\Features\Schemas\FeatureForm;
use App\Models\Feature;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RoomTypeForm
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
                ->columnSpanFull()
                ->unique(ignoreRecord: true),
            TextInput::make('base_price')
                ->label('Precio Base')
                ->numeric()
                ->prefix('S/')
                ->default(0.00)
                ->required(),
            TextInput::make('capacity')
                ->label('Capacidad (Personas)')
                ->numeric()
                ->default(1)
                ->required(),
            Select::make('features')
                ->label('Características')
                ->multiple()
                ->columnSpanFull()
                ->preload()
                ->relationship('features', 'name')
                ->getOptionLabelFromRecordUsing(fn (Feature $record) => $record->name_with_price)
                ->createOptionForm(FeatureForm::fields()),
            Toggle::make('is_active')
                ->label('¿Está activo?')
                ->default(true)
                ->helperText('Al desactivar este tipo de habitación, no podrás asignarla a una nueva habitación.'),

        ];
    }
}
