<?php

namespace App\Filament\Resources\Rooms\Schemas;

use App\Enums\RoomStatus;
use App\Models\Feature;
use App\Models\Floor;
use App\Models\RoomType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RoomForm
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
            Flex::make([

                Select::make('floor_id')
                    ->label('Piso')
                    ->relationship('floor', 'name')
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                        if ($state) {
                            $floor = Floor::find($state);
                            if ($floor && strlen($floor->name) >= 2) {
                                if (blank($get('number'))) {
                                    $set('number', substr($floor->name, 0, 2));
                                }
                            }
                        } else {
                            $set('number', null);
                        }
                    }),
                TextInput::make('number')
                    ->label('Número')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (Get $get) => ! $get('floor_id'))
                    ->dehydrated(),
                Select::make('room_type_id')
                    ->label('Tipo de Habitación')
                    ->relationship('roomType', 'name')
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        if ($state) {
                            $roomType = RoomType::find($state);
                            if ($roomType) {
                                $set('room_type_features', $roomType->features->pluck('id')->toArray());
                            }
                        } else {
                            $set('room_type_features', []);
                        }
                    }),
            ])
                ->columns(3)
                ->columnSpanFull(),
            Select::make('room_type_features')
                ->label('Características del Tipo')
                ->multiple()
                ->disabled()
                ->dehydrated(false)
                ->columnSpanFull()
                ->options(fn () => Feature::all()->mapWithKeys(fn ($f) => [$f->id => $f->name_with_price])->toArray())
                ->afterStateHydrated(function (Select $component, Get $get) {
                    $roomTypeId = $get('room_type_id');
                    if ($roomTypeId) {
                        $roomType = RoomType::find($roomTypeId);
                        if ($roomType) {
                            $component->state($roomType->features->pluck('id')->toArray());
                        }
                    }
                })
                ->visible(fn (Get $get) => filled($get('room_type_id'))),
            TextInput::make('location')
                ->label('Ubicación física / Detalles de acceso')
                ->columnSpanFull(),
            Select::make('status')
                ->label('Estado')
                ->disabled()
                ->dehydrated()
                ->options(RoomStatus::class)
                ->default(RoomStatus::AVAILABLE)
                ->required()
                ->native(false),
            DateTimePicker::make('last_cleaned_at')
                ->label('Última limpieza')
                ->disabled(),
            Flex::make([
                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Notas de la habitación')
                    ->columnSpanFull(),
            ])
                ->columnSpanFull()
                ->columns(2),
        ];
    }
}
