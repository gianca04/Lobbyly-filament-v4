<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\DocumentType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
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
                Select::make('tipo_documento')
                    ->options(DocumentType::class)
                    ->required()
                    ->native(false)
                    ->validationMessages([
                        'required' => 'El campo es requerido.',
                    ]),
                TextInput::make('numero_documento')
                    ->required()
                    ->validationMessages([
                        'required' => 'El campo es requerido.',
                    ]),
                TextInput::make('nombres')
                    ->required()
                    ->validationMessages([
                        'required' => 'El campo es requerido.',
                    ]),
                TextInput::make('apellidos')
                    ->required()
                    ->validationMessages([
                        'required' => 'El campo es requerido.',
                    ]),
                DatePicker::make('fecha_nacimiento')
                    ->required()
                    ->validationMessages([
                        'required' => 'El campo es requerido.',
                    ])
                    ->prefixIcon('heroicon-m-cake'),
                TextInput::make('telefono')
                    ->required()
                    ->prefixIcon('heroicon-m-phone')
                    ->validationMessages([
                        'required' => 'El campo es requerido.',
                    ]),
                TextInput::make('direccion')
                    ->prefixIcon('heroicon-m-map-pin'),
                FileUpload::make('photo_path')
                    ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('employee-photos')
                    ->avatar(),
            ])
                ->columns(2)
        ]));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::fields());
    }
}
