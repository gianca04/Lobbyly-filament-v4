<?php

declare(strict_types=1);

namespace App\Filament\Resources\Movements\Pages;

use App\Filament\Resources\Movements\MovementResource;
use Filament\Resources\Pages\Page;

/**
 * Página de creación de movimientos de inventario.
 *
 * Renderiza la vista Blade unificada en modo 'create', permitiendo
 * seleccionar el tipo de movimiento y completar el formulario
 * dinámico correspondiente. Toda la lógica del formulario
 * se maneja con JavaScript vanilla vía API REST.
 */
class CreateMovement extends Page
{
    protected static string $resource = MovementResource::class;

    protected string $view = 'filament.resources.movements.movement-form';

    protected static ?string $title = 'Registrar Movimiento';

    /**
     * Variables pasadas a la vista Blade.
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'mode' => 'create',
            'movement' => null,
            'listUrl' => MovementResource::getUrl(),
        ];
    }
}
