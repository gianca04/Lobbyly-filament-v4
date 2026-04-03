<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

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
                ->required(),
            TextInput::make('email')
                ->label('Email address')
                ->email()
                ->required(),
            DateTimePicker::make('email_verified_at'),
            TextInput::make('password')
                ->password()
                ->required(),
            Toggle::make('is_active')
                ->default(true)
                ->columnSpanFull(),
        ];
    }
}
