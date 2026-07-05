<?php

namespace App\Filament\Resources\Floors\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FloorForm
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
                ->columnSpanFull()
                ->required()
                ->unique(ignoreRecord: true),
            Textarea::make('description')
                ->label('Descripción')
                ->columnSpanFull(),
        ];
    }
}
