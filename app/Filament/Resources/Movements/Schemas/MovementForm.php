<?php

namespace App\Filament\Resources\Movements\Schemas;

use App\Enums\MovementType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles del Movimiento')
                    ->columnSpanFull()
                    ->description('Registre los detalles de la transacción de inventario.')
                    ->schema([
                        Select::make('item_id')
                            ->label('Artículo')
                            ->relationship('item', 'name')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        Select::make('location_id')
                            ->label('Ubicación')
                            ->relationship('location', 'name')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        Select::make('user_id')
                            ->label('Responsable')
                            ->relationship('user', 'name')
                            ->required()
                            ->default(auth()->id())
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        Select::make('type')
                            ->label('Tipo de Movimiento')
                            ->options(MovementType::class)
                            ->required()
                            ->native(false),
                        TextInput::make('quantity')
                            ->label('Cantidad')
                            ->required()
                            ->numeric()
                            ->default(1.0),
                        Textarea::make('notes')
                            ->label('Observaciones')
                            ->placeholder('Ej. Ajuste por daño, transferencia a sucursal, etc.')
                            ->default(null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
