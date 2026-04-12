<?php

declare(strict_types=1);

namespace App\Filament\Resources\Movements\Pages;

use App\Filament\Resources\Movements\MovementResource;
use App\Models\Movement;
use Filament\Resources\Pages\Page;

/**
 * Página de visualización de un movimiento de inventario.
 *
 * Renderiza la vista Blade unificada en modo 'view' (solo lectura).
 * Carga el movimiento con sus relaciones y pasa los datos
 * a la misma Blade view que usa CreateMovement.
 *
 * Los movimientos son inmutables: no existe opción de edición.
 */
class ViewMovement extends Page
{
    protected static string $resource = MovementResource::class;

    protected string $view = 'filament.resources.movements.movement-form';

    protected static ?string $title = 'Detalle del Movimiento';

    public Movement $record;

    /**
     * Monta la página cargando el movimiento con sus relaciones.
     */
    public function mount(int|string $record): void
    {
        $this->record = Movement::with(['item', 'location', 'user'])->findOrFail($record);
    }

    /**
     * Variables pasadas a la vista Blade.
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'mode' => 'view',
            'movement' => $this->record,
            'listUrl' => MovementResource::getUrl(),
        ];
    }
}
