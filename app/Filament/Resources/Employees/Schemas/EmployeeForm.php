<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\DocumentType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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
                Select::make('tipo_documento')
                    ->options(DocumentType::class)
                    ->required()
                    ->native(false),
                TextInput::make('numero_documento')
                    ->required(),
                TextInput::make('nombres')
                    ->required(),
                TextInput::make('apellidos')
                    ->required(),
                DatePicker::make('fecha_nacimiento')
                    ->required(),
                TagsInput::make('telefono')
                    ->required()
                    ->separator(','),
                TagsInput::make('direccion')
                    ->separator(','),
                FileUpload::make('photo_path')
                    ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('employee-photos')
                    ->avatar(),
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
