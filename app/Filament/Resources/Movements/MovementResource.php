<?php

namespace App\Filament\Resources\Movements;

use App\Filament\Resources\Movements\Pages\CreateMovement;
use App\Filament\Resources\Movements\Pages\ListMovements;
use App\Filament\Resources\Movements\Pages\ViewMovement;
use App\Filament\Resources\Movements\Schemas\MovementForm;
use App\Filament\Resources\Movements\Tables\MovementsTable;
use App\Models\Movement;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MovementResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static ?string $label = 'Movimiento';

    protected static ?string $pluralLabel = 'Movimientos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Administrar movimientos')
                ->icon(Heroicon::OutlinedArrowsRightLeft)
                ->label('Movimientos')
                ->group('Inventario')
                ->url(static::getUrl())
                ->sort(2),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return MovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovementsTable::configure($table);
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
            'index' => ListMovements::route('/'),
            'create' => CreateMovement::route('/create'),
            'view' => ViewMovement::route('/{record}'),
        ];
    }
}
