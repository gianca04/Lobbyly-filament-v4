<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierForm
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
                ->label('Nombre o Razón Social')
                ->required(),
            Repeater::make('email')
                ->label('Correos electrónicos')
                ->simple(
                    TextInput::make('email')
                        ->email()
                        ->required(),
                )
                ->default([])
                ->columnSpanFull(),
            Repeater::make('phone')
                ->label('Teléfonos')
                ->simple(
                    TextInput::make('phone')
                        ->tel()
                        ->required(),
                )
                ->default([])
                ->columnSpanFull(),
        ];
    }
}
