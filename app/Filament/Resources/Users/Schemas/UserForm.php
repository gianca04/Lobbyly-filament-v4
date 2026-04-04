<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('MainTabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Información del Empleado')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Group::make()
                                    ->relationship('employee')
                                    ->schema(EmployeeForm::fields(includeUserId: false)),
                            ]),
                        Tab::make('Información del Usuario')
                            ->icon('heroicon-m-lock-closed')
                            ->columns(2)
                            ->schema(self::userFields()),

                    ]),
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    private static function userFields(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->validationMessages([
                    'required' => 'El campo es requerido.',
                ]),
            TextInput::make('email')
                ->label('Email address')
                ->email()
                ->required()
                ->validationMessages([
                    'required' => 'El campo es requerido.',
                ]),
            TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->required(fn (string $operation, Get $get): bool => $operation === 'create' && $get('is_active'))
                ->visible(
                    fn (string $context, Get $get): bool => $context === 'create' || $context === 'edit' && $get('is_active')
                )
                ->confirmed(),

            TextInput::make('password_confirmation')
                ->password()
                ->label('Confirmar Contraseña')
                ->required(
                    fn (string $context, Get $get): bool => $context === 'create' || $context === 'edit' && $get('is_active')
                )
                ->visible(
                    fn (string $context, Get $get): bool => $context === 'create' || $context === 'edit' && $get('is_active')
                ),
            Toggle::make('is_active')
                ->label('Activo')
                ->default(true),
            Select::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->searchable(),
        ];
    }
}
