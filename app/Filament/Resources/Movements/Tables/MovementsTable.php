<?php

declare(strict_types=1);

namespace App\Filament\Resources\Movements\Tables;

use App\Enums\MovementType;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Configuración de la tabla de movimientos de inventario.
 *
 * Muestra el historial de movimientos con badges de color por tipo,
 * cantidad con signo, filtros por tipo/artículo y acciones por fila.
 */
class MovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('item.name')
                    ->label('Artículo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location.name')
                    ->label('Ubicación')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Responsable')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (MovementType $state): string => $state->label())
                    ->color(fn (MovementType $state): string => match ($state) {
                        MovementType::INPUT => 'success',
                        MovementType::OUTPUT => 'danger',
                        MovementType::TRANSFER => 'info',
                        MovementType::ADJUSTMENT => 'warning',
                        MovementType::CONSUMPTION => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable()
                    ->alignRight()
                    ->color(fn ($record): string => match (true) {
                        $record->isInput() => 'success',
                        $record->isOutput(), $record->isConsumption() => 'danger',
                        $record->isAdjustment() && (float) $record->quantity >= 0 => 'success',
                        $record->isAdjustment() && (float) $record->quantity < 0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($record): string {
                        $qty = (float) $record->quantity;
                        $prefix = match (true) {
                            $record->isInput() => '+',
                            $record->isOutput(), $record->isConsumption() => '-',
                            $record->isAdjustment() && $qty >= 0 => '+',
                            default => '',
                        };

                        return $prefix.number_format(abs($qty), 2);
                    }),
                TextColumn::make('notes')
                    ->label('Observaciones')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo de Movimiento')
                    ->options(
                        collect(MovementType::cases())
                            ->mapWithKeys(fn (MovementType $type) => [$type->value => $type->label()])
                            ->toArray()
                    )
                    ->native(false),
                SelectFilter::make('item_id')
                    ->label('Artículo')
                    ->relationship('item', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                SelectFilter::make('location_id')
                    ->label('Ubicación')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
