<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\DocumentType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function fields(bool $includeUserId = true): array
    {
        return array_values(array_filter([
            $includeUserId
                ? TextInput::make('user_id')
                ->required()
                ->numeric()
                : null,
            Group::make([
                FileUpload::make('photo_path')
                    ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('employee-photos')
                    ->avatar(),
                Group::make([
                    Select::make('document_type')
                        ->label('Tipo de Documento')
                        ->options(DocumentType::class)
                        ->required()
                        ->native(false),
                    TextInput::make('document_number')
                        ->label('Número de Documento')
                        ->required(),

                ])->columns(1)
            ])
                ->columns(2),
            Group::make([
                DatePicker::make('birth_date')
                    ->label('Fecha de Nacimiento')
                    ->prefixIcon('heroicon-o-calendar')
                    ->required(),
                TextInput::make('first_name')
                    ->label('Nombres')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('last_name')
                    ->label('Apellidos')
                    ->required()
                    ->columnSpanFull(),
                Repeater::make('phones')
                    ->label('Teléfonos')
                    ->collapsible()
                    ->collapsed()
                    ->defaultItems(0)
                    ->itemLabel(fn(array $state): ?string => $state['phone'] ?? null)
                    ->reorderable(false)
                    ->schema([
                        TextInput::make('phone')
                            ->label('Número de Teléfono')
                            ->prefixIcon('heroicon-o-phone'),
                    ])
                    ->addActionLabel('Agregar Teléfono'),
                Repeater::make('addresses')
                    ->label('Direcciones')
                    ->collapsed()
                    ->collapsible()
                    ->reorderable(false)
                    ->itemLabel(fn(array $state): ?string => $state['address'] ?? null)
                    ->defaultItems(0)
                    ->schema([
                        TextInput::make('address')
                            ->label('Dirección')
                            ->prefixIcon('heroicon-o-map-pin'),
                    ])
                    ->addActionLabel('Agregar Dirección'),

            ])
                ->columns(2),
        ]));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::fields());
    }
}
