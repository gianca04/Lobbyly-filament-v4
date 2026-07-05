<?php

namespace App\Filament\Resources\Floors;

use App\Filament\Resources\Floors\Pages\ListFloors;
use App\Filament\Resources\Floors\Schemas\FloorForm;
use App\Filament\Resources\Floors\Tables\FloorsTable;
use App\Models\Floor;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FloorResource extends Resource
{
    protected static ?string $model = Floor::class;

    protected static ?string $label = 'Piso';

    protected static ?string $pluralLabel = 'Pisos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Administrar pisos')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->label('Pisos')
                ->group('Gestión de habitaciones')
                ->url(static::getUrl())
                ->sort(3),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return FloorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FloorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFloors::route('/'),
        ];
    }
}
