<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemLocation;
use App\Models\Location;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador de API interna para consultas de inventario.
 *
 * Provee endpoints ligeros consumidos por la vista Blade
 * de movimientos para poblar selects y mostrar stock
 * en tiempo real vía fetch() desde JavaScript.
 *
 * Estos endpoints NO modifican datos, solo consultan.
 */
class InventoryApiController extends Controller
{
    /**
     * Lista todos los artículos disponibles.
     *
     * Retorna id, nombre, SKU y stock total para poblar
     * los selects de artículos en el formulario.
     */
    public function items(): JsonResponse
    {
        $items = Item::query()
            ->select(['id', 'name', 'sku', 'current_stock'])
            ->orderBy('name')
            ->get();

        return response()->json($items);
    }

    /**
     * Lista todas las ubicaciones disponibles.
     *
     * Retorna id y nombre para poblar los selects de ubicaciones.
     */
    public function locations(): JsonResponse
    {
        $locations = Location::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return response()->json($locations);
    }

    /**
     * Obtiene el stock de un artículo en una ubicación específica.
     *
     * Usado por el JS para mostrar stock disponible en tiempo real
     * al seleccionar artículo + ubicación en salidas y ajustes.
     *
     * @param  Request  $request  Debe incluir item_id y location_id.
     */
    public function stock(Request $request, InventoryService $inventoryService): JsonResponse
    {
        $request->validate([
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ]);

        $stock = $inventoryService->getStockAtLocation(
            (int) $request->input('item_id'),
            (int) $request->input('location_id'),
        );

        return response()->json(['stock' => $stock]);
    }

    /**
     * Lista las ubicaciones con stock de un artículo específico.
     *
     * Retorna cada ubicación que tiene stock > 0 del artículo,
     * incluyendo el nombre y la cantidad disponible.
     * Útil para filtrar ubicaciones válidas en salidas y transferencias.
     *
     * @param  Request  $request  Debe incluir item_id.
     */
    public function itemLocations(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => ['required', 'integer', 'exists:items,id'],
        ]);

        $itemLocations = ItemLocation::where('item_id', $request->input('item_id'))
            ->where('quantity', '>', 0)
            ->with('location:id,name')
            ->get()
            ->map(fn (ItemLocation $pivot) => [
                'location_id' => $pivot->location_id,
                'location_name' => $pivot->location->name,
                'quantity' => (float) $pivot->quantity,
            ]);

        return response()->json($itemLocations);
    }

    /**
     * Lista TODAS las ubicaciones con el stock actual de un artículo.
     *
     * A diferencia de itemLocations, este incluye ubicaciones con stock 0.
     * Útil para destinos de transferencia o ingresos.
     *
     * @param  Request  $request  Debe incluir item_id.
     */
    public function allLocationsWithStock(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => ['required', 'integer', 'exists:items,id'],
        ]);

        $itemId = $request->input('item_id');
        
        $locations = Location::select(['id', 'name'])->orderBy('name')->get();
        
        $itemLocations = ItemLocation::where('item_id', $itemId)
            ->get(['location_id', 'quantity'])
            ->keyBy('location_id');

        $result = $locations->map(fn (Location $loc) => [
            'location_id' => $loc->id,
            'location_name' => $loc->name,
            'quantity' => (float) ($itemLocations->get($loc->id)?->quantity ?? 0),
        ]);

        return response()->json($result);
    }
}
