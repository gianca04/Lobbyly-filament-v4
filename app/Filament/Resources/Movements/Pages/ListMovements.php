<?php

declare(strict_types=1);

namespace App\Filament\Resources\Movements\Pages;

use App\Actions\ProcessAdjustmentMovementAction;
use App\Actions\ProcessInputMovementAction;
use App\Actions\ProcessOutputMovementAction;
use App\Actions\ProcessTransferMovementAction;
use App\DTOs\AdjustmentMovementData;
use App\DTOs\InputMovementData;
use App\DTOs\MovementData;
use App\DTOs\TransferMovementData;
use App\Enums\MovementType;
use App\Filament\Resources\Movements\MovementResource;
use App\Models\Item;
use App\Models\ItemLocation;
use App\Models\Location;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

/**
 * Página de listado de movimientos con acciones modales para cada tipo.
 *
 * Sustituye el botón genérico "Crear" por 4 acciones especializadas
 * que delegan a los Actions de la capa de negocio.
 */
class ListMovements extends ListRecords
{
    protected static string $resource = MovementResource::class;

    /**
     * Define las 4 acciones del header: Ingreso, Salida, Transferencia, Ajuste.
     *
     * Cada acción abre un modal con formulario nativo Filament
     * y delega la ejecución al Action correspondiente.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            $this->makeInputAction(),
            $this->makeOutputAction(),
            $this->makeTransferAction(),
            $this->makeAdjustmentAction(),
        ];
    }

    /**
     * Acción: Registrar Ingreso Masivo.
     *
     * Permite distribuir un ingreso en múltiples ubicaciones usando Repeater.
     * Cada fila tiene una ubicación y una cantidad.
     */
    private function makeInputAction(): Action
    {
        return Action::make('registerInput')
            ->label('Registrar Ingreso')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('success')
            ->modalHeading(' Registrar Ingreso de Artículos')
            ->modalDescription('Distribuya el ingreso en una o más ubicaciones.')
            ->modalSubmitActionLabel('Registrar Ingreso')
            ->schema([
                Select::make('item_id')
                    ->label('Artículo')
                    ->options(Item::pluck('name', 'id'))
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload(),

                Repeater::make('distributions')
                    ->label('Distribuciones por Ubicación')
                    ->schema([
                        Select::make('location_id')
                            ->label('Ubicación')
                            ->relationship('location', 'name')
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
                    ])
                    ->columns(2)
                    ->minItems(1)
                    ->defaultItems(1)
                    ->addActionLabel('Agregar ubicación')
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Observaciones')
                    ->placeholder('Ej. Compra de 3 cajas de jabones, factura #001.')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                try {
                    $dto = new InputMovementData(
                        itemId: (int) $data['item_id'],
                        userId: (int) Auth::id(),
                        distributions: $data['distributions'],
                        notes: $data['notes'] ?? null,
                    );

                    $action = app(ProcessInputMovementAction::class);
                    $movements = $action->execute($dto);

                    Notification::make()
                        ->title('Ingreso registrado exitosamente')
                        ->body("Se crearon {$movements->count()} movimiento(s) de ingreso.")
                        ->success()
                        ->send();
                } catch (\DomainException $e) {
                    Notification::make()
                        ->title('Error al registrar ingreso')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Acción: Registrar Salida.
     *
     * Extrae artículos de una ubicación específica.
     * Valida stock suficiente antes de procesar.
     */
    private function makeOutputAction(): Action
    {
        return Action::make('registerOutput')
            ->label('Registrar Salida')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->color('danger')
            ->modalHeading('Registrar Salida de Artículos')
            ->modalDescription('Extraiga artículos de una ubicación específica.')
            ->modalSubmitActionLabel('Registrar Salida')
            ->schema([
                Select::make('item_id')
                    ->label('Artículo')
                    ->options(Item::pluck('name', 'id'))
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->live(),

                Select::make('location_id')
                    ->label('Ubicación')
                    ->options(function (Get $get): array {
                        $itemId = $get('item_id');
                        if (! $itemId) {
                            return Location::pluck('name', 'id')->toArray();
                        }

                        /** Mostrar solo ubicaciones donde hay stock del artículo */
                        return ItemLocation::where('item_id', $itemId)
                            ->where('quantity', '>', 0)
                            ->with('location')
                            ->get()
                            ->mapWithKeys(fn($pivot) => [
                                $pivot->location_id => "{$pivot->location->name} (Stock: {$pivot->quantity})",
                            ])
                            ->toArray();
                    })
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
                    ->placeholder('Ej. Entrega a cliente, despacho a habitación, etc.')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                try {
                    $dto = new MovementData(
                        itemId: (int) $data['item_id'],
                        locationId: (int) $data['location_id'],
                        userId: (int) Auth::id(),
                        type: MovementType::OUTPUT,
                        quantity: (float) $data['quantity'],
                        notes: $data['notes'] ?? null,
                    );

                    $action = app(ProcessOutputMovementAction::class);
                    $action->execute($dto);

                    Notification::make()
                        ->title('Salida registrada exitosamente')
                        ->body("Se extrajeron {$data['quantity']} unidades.")
                        ->success()
                        ->send();
                } catch (\DomainException $e) {
                    Notification::make()
                        ->title('Stock insuficiente')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Acción: Registrar Transferencia.
     *
     * Mueve artículos de una ubicación a otra.
     * Genera 2 movimientos independientes (salida + ingreso).
     */
    private function makeTransferAction(): Action
    {
        return Action::make('registerTransfer')
            ->label('Registrar Transferencia')
            ->icon(Heroicon::OutlinedArrowsRightLeft)
            ->color('info')
            ->modalHeading('Registrar Transferencia')
            ->modalDescription('Mueva artículos entre ubicaciones.')
            ->modalSubmitActionLabel('Registrar Transferencia')
            ->schema([
                Select::make('item_id')
                    ->label('Artículo')
                    ->options(Item::pluck('name', 'id'))
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->live(),

                Select::make('origin_location_id')
                    ->label('Ubicación de Origen')
                    ->options(function (Get $get): array {
                        $itemId = $get('item_id');
                        if (! $itemId) {
                            return Location::pluck('name', 'id')->toArray();
                        }

                        return ItemLocation::where('item_id', $itemId)
                            ->where('quantity', '>', 0)
                            ->with('location')
                            ->get()
                            ->mapWithKeys(fn($pivot) => [
                                $pivot->location_id => "{$pivot->location->name} (Stock: {$pivot->quantity})",
                            ])
                            ->toArray();
                    })
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
                    ->placeholder('Ej. Transferencia a sucursal, reubicación de estante, etc.')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                try {
                    $dto = new TransferMovementData(
                        itemId: (int) $data['item_id'],
                        userId: (int) Auth::id(),
                        originLocationId: (int) $data['origin_location_id'],
                        destinationLocationId: (int) $data['destination_location_id'],
                        quantity: (float) $data['quantity'],
                        notes: $data['notes'] ?? null,
                    );

                    $action = app(ProcessTransferMovementAction::class);
                    $action->execute($dto);

                    Notification::make()
                        ->title('Transferencia registrada exitosamente')
                        ->body("Se transfirieron {$data['quantity']} unidades.")
                        ->success()
                        ->send();
                } catch (\DomainException $e) {
                    Notification::make()
                        ->title('Error en la transferencia')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Acción: Registrar Ajuste.
     *
     * Corrige el stock cuando el conteo real difiere del registrado.
     * Muestra el stock actual como referencia visual.
     */
    private function makeAdjustmentAction(): Action
    {
        return Action::make('registerAdjustment')
            ->label('Registrar Ajuste')
            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
            ->color('warning')
            ->modalHeading(' Registrar Ajuste de Inventario')
            ->modalDescription('Corrija el stock cuando el conteo real no coincide con el registrado.')
            ->modalSubmitActionLabel('Registrar Ajuste')
            ->schema([
                Select::make('item_id')
                    ->label('Artículo')
                    ->options(Item::pluck('name', 'id'))
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->live(),

                Select::make('location_id')
                    ->label('Ubicación')
                    ->options(function (Get $get): array {
                        $itemId = $get('item_id');
                        if (! $itemId) {
                            return Location::pluck('name', 'id')->toArray();
                        }

                        return ItemLocation::where('item_id', $itemId)
                            ->with('location')
                            ->get()
                            ->mapWithKeys(fn($pivot) => [
                                $pivot->location_id => "{$pivot->location->name} (Stock registrado: {$pivot->quantity})",
                            ])
                            ->toArray();
                    })
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->live(),

                TextInput::make('current_stock_display')
                    ->label('Stock registrado actual')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(function (Get $get): string {
                        $itemId = $get('item_id');
                        $locationId = $get('location_id');

                        if (! $itemId || ! $locationId) {
                            return 'Seleccione artículo y ubicación';
                        }

                        $inventoryService = app(InventoryService::class);
                        $stock = $inventoryService->getStockAtLocation((int) $itemId, (int) $locationId);

                        return (string) $stock;
                    }),

                TextInput::make('new_quantity')
                    ->label('Cantidad real contabilizada')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Introduzca la cantidad exacta contada físicamente.'),

                Textarea::make('notes')
                    ->label('Observaciones')
                    ->placeholder('Ej. Faltante detectado en reconteo mensual, producto dañado, etc.')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                try {
                    $dto = new AdjustmentMovementData(
                        itemId: (int) $data['item_id'],
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
                        ->title('Ajuste registrado exitosamente')
                        ->body("Diferencia aplicada: {$sign}{$difference} unidades.")
                        ->success()
                        ->send();
                } catch (\DomainException $e) {
                    Notification::make()
                        ->title('Error en el ajuste')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
