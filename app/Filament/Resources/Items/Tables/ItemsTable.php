<?php

declare(strict_types=1);

namespace App\Filament\Resources\Items\Tables;

use App\Actions\ProcessAdjustmentMovementAction;
use App\Actions\ProcessInputMovementAction;
use App\Actions\ProcessOutputMovementAction;
use App\Actions\ProcessTransferMovementAction;
use App\DTOs\AdjustmentMovementData;
use App\DTOs\InputMovementData;
use App\DTOs\MovementData;
use App\DTOs\TransferMovementData;
use App\Enums\MovementType;
use App\Models\ItemLocation;
use App\Models\Location;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * Configuración de la tabla de artículos del inventario.
 *
 * Incluye alerta visual de stock bajo (rojo cuando current_stock < minimum_stock)
 * y acciones rápidas por fila para ingreso/salida directos.
 */
class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('unitOfMeasure.name')
                    ->label('Unidad')
                    ->searchable(),
                TextColumn::make('unit_cost')
                    ->label('Costo Unit.')
                    ->money('PEN')
                    ->sortable(),
                TextColumn::make('current_stock')
                    ->label('Stock Actual')
                    ->prefix(fn ($record): string => $record->unitOfMeasure?->symbol.' ')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record): string => $record->isStockBelowMinimum() ? 'danger' : 'success')
                    ->icon(
                        fn ($record): ?string => $record->isStockBelowMinimum()
                            ? 'heroicon-o-exclamation-triangle'
                            : null
                    )
                    ->tooltip(
                        fn ($record): ?string => $record->isStockBelowMinimum()
                            ? "⚠ Stock por debajo del mínimo ({$record->minimum_stock})"
                            : null
                    ),
                TextColumn::make('minimum_stock')
                    ->label('Stock Mín.')
                    ->prefix(fn ($record): string => $record->unitOfMeasure?->symbol.' ')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('quickInput')
                        ->label('Ingreso rápido')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('success')
                        ->modalHeading(fn ($record): string => " Ingreso: {$record->name}")
                        ->modalSubmitActionLabel('Registrar')
                        ->schema([
                            Select::make('location_id')
                                ->label('Ubicación')
                                ->options(Location::pluck('name', 'id'))
                                ->required()
                                ->native(false)
                                ->searchable(),
                            TextInput::make('quantity')
                                ->label('Cantidad')
                                ->required()
                                ->numeric()
                                ->minValue(0.01)
                                ->default(1),
                            Textarea::make('notes')
                                ->label('Observaciones')
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data): void {
                            try {
                                $dto = new InputMovementData(
                                    itemId: (int) $record->id,
                                    userId: (int) Auth::id(),
                                    distributions: [
                                        [
                                            'location_id' => (int) $data['location_id'],
                                            'quantity' => (float) $data['quantity'],
                                        ],
                                    ],
                                    notes: $data['notes'] ?? null,
                                );

                                $action = app(ProcessInputMovementAction::class);
                                $action->execute($dto);

                                Notification::make()
                                    ->title('Ingreso registrado')
                                    ->body("Se ingresaron {$data['quantity']} unidades de {$record->name}.")
                                    ->success()
                                    ->send();
                            } catch (\DomainException $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('quickOutput')
                        ->label('Salida rápida')
                        ->icon(Heroicon::OutlinedArrowUpTray)
                        ->color('danger')
                        ->modalHeading(fn ($record): string => "Inventario Salida: {$record->name}")
                        ->modalSubmitActionLabel('Registrar')
                        ->schema(fn ($record): array => [
                            Select::make('location_id')
                                ->label('Ubicación')
                                ->options(
                                    ItemLocation::where('item_id', $record->id)
                                        ->where('quantity', '>', 0)
                                        ->with('location')
                                        ->get()
                                        ->mapWithKeys(fn ($pivot) => [
                                            $pivot->location_id => "{$pivot->location->name} (Stock: {$pivot->quantity})",
                                        ])
                                        ->toArray()
                                )
                                ->required()
                                ->native(false)
                                ->searchable(),
                            TextInput::make('quantity')
                                ->label('Cantidad')
                                ->required()
                                ->numeric()
                                ->minValue(0.01)
                                ->default(1),
                            Textarea::make('notes')
                                ->label('Observaciones')
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data): void {
                            try {
                                $dto = new MovementData(
                                    itemId: (int) $record->id,
                                    locationId: (int) $data['location_id'],
                                    userId: (int) Auth::id(),
                                    type: MovementType::OUTPUT,
                                    quantity: (float) $data['quantity'],
                                    notes: $data['notes'] ?? null,
                                );

                                $action = app(ProcessOutputMovementAction::class);
                                $action->execute($dto);

                                Notification::make()
                                    ->title('Salida registrada')
                                    ->body("Se extrajeron {$data['quantity']} unidades de {$record->name}.")
                                    ->success()
                                    ->send();
                            } catch (\DomainException $e) {
                                Notification::make()
                                    ->title('Stock insuficiente')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('quickTransfer')
                        ->label('Transferencia')
                        ->icon(Heroicon::OutlinedArrowsRightLeft)
                        ->color('info')
                        ->modalHeading(fn ($record): string => "Transferencia: {$record->name}")
                        ->modalDescription('Mueva unidades de una ubicación a otra.')
                        ->modalSubmitActionLabel('Transferir')
                        ->schema(fn ($record): array => [
                            Select::make('origin_location_id')
                                ->label('Ubicación de Origen')
                                ->options(
                                    ItemLocation::where('item_id', $record->id)
                                        ->where('quantity', '>', 0)
                                        ->with('location')
                                        ->get()
                                        ->mapWithKeys(fn ($pivot) => [
                                            $pivot->location_id => "{$pivot->location->name} (Stock: {$pivot->quantity})",
                                        ])
                                        ->toArray()
                                )
                                ->required()
                                ->native(false)
                                ->searchable(),
                            Select::make('destination_location_id')
                                ->label('Ubicación de Destino')
                                ->options(Location::pluck('name', 'id'))
                                ->required()
                                ->native(false)
                                ->searchable()
                                ->different('origin_location_id'),
                            TextInput::make('quantity')
                                ->label('Cantidad')
                                ->required()
                                ->numeric()
                                ->minValue(0.01)
                                ->default(1),
                            Textarea::make('notes')
                                ->label('Observaciones')
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data): void {
                            try {
                                $dto = new TransferMovementData(
                                    itemId: (int) $record->id,
                                    userId: (int) Auth::id(),
                                    originLocationId: (int) $data['origin_location_id'],
                                    destinationLocationId: (int) $data['destination_location_id'],
                                    quantity: (float) $data['quantity'],
                                    notes: $data['notes'] ?? null,
                                );

                                $action = app(ProcessTransferMovementAction::class);
                                $action->execute($dto);

                                Notification::make()
                                    ->title('Transferencia registrada')
                                    ->body("Se transfirieron {$data['quantity']} unidades de {$record->name}.")
                                    ->success()
                                    ->send();
                            } catch (\DomainException $e) {
                                Notification::make()
                                    ->title('Error en la transferencia')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('quickAdjustment')
                        ->label('Ajuste')
                        ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                        ->color('warning')
                        ->modalHeading(fn ($record): string => "Ajuste: {$record->name}")
                        ->modalDescription('Corrija el stock cuando el conteo real no coincide.')
                        ->modalSubmitActionLabel('Ajustar')
                        ->schema(fn ($record): array => [
                            Select::make('location_id')
                                ->label('Ubicación')
                                ->options(
                                    ItemLocation::where('item_id', $record->id)
                                        ->with('location')
                                        ->get()
                                        ->mapWithKeys(fn ($pivot) => [
                                            $pivot->location_id => "{$pivot->location->name} (Stock registrado: {$pivot->quantity})",
                                        ])
                                        ->toArray()
                                )
                                ->required()
                                ->native(false)
                                ->searchable(),
                            TextInput::make('new_quantity')
                                ->label('Cantidad real contabilizada')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->helperText('Introduzca la cantidad exacta contada físicamente.'),
                            Textarea::make('notes')
                                ->label('Observaciones')
                                ->placeholder('Motivo del ajuste: faltante, sobrante, daño, etc.')
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data): void {
                            try {
                                $dto = new AdjustmentMovementData(
                                    itemId: (int) $record->id,
                                    userId: (int) Auth::id(),
                                    locationId: (int) $data['location_id'],
                                    newQuantity: (float) $data['new_quantity'],
                                    notes: $data['notes'] ?? null,
                                );

                                $action = app(ProcessAdjustmentMovementAction::class);
                                $movement = $action->execute($dto);

                                $difference = (float) $movement->quantity;
                                $sign = $difference >= 0 ? '+' : '';

                                Notification::make()
                                    ->title('Ajuste registrado')
                                    ->body("Diferencia aplicada: {$sign}{$difference} unidades en {$record->name}.")
                                    ->success()
                                    ->send();
                            } catch (\DomainException $e) {
                                Notification::make()
                                    ->title('Error en el ajuste')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
