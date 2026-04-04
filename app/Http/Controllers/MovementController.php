<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ProcessAdjustmentMovementAction;
use App\Actions\ProcessInputMovementAction;
use App\Actions\ProcessOutputMovementAction;
use App\Actions\ProcessTransferMovementAction;
use App\DTOs\AdjustmentMovementData;
use App\DTOs\InputMovementData;
use App\DTOs\MovementData;
use App\DTOs\TransferMovementData;
use App\Enums\MovementType;
use App\Http\Requests\StoreAdjustmentMovementRequest;
use App\Http\Requests\StoreInputMovementRequest;
use App\Http\Requests\StoreOutputMovementRequest;
use App\Http\Requests\StoreTransferMovementRequest;
use App\Http\Resources\MovementResource;
use App\Models\Movement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controlador REST para movimientos de inventario.
 *
 * Actúa como "Recepcionista": recibe las peticiones validadas,
 * construye los DTOs correspondientes y delega la ejecución
 * a los Actions especializados.
 *
 * Cada tipo de movimiento tiene su propio endpoint para
 * mantener la separación de responsabilidades.
 */
class MovementController extends Controller
{
    /**
     * Lista todos los movimientos con filtros opcionales.
     *
     * Soporta filtrado por: type, item_id, location_id.
     * Incluye relaciones: item, location, user.
     */
    public function index(): AnonymousResourceCollection
    {
        $movements = Movement::query()
            ->with(['item', 'location', 'user'])
            ->when(request('type'), fn ($query, $type) => $query->where('type', $type))
            ->when(request('item_id'), fn ($query, $itemId) => $query->where('item_id', $itemId))
            ->when(request('location_id'), fn ($query, $locationId) => $query->where('location_id', $locationId))
            ->latest()
            ->paginate(15);

        return MovementResource::collection($movements);
    }

    /**
     * Muestra un movimiento específico con sus relaciones.
     *
     * @param  Movement  $movement  Movimiento a consultar (Route Model Binding).
     */
    public function show(Movement $movement): MovementResource
    {
        $movement->load(['item', 'location', 'user']);

        return new MovementResource($movement);
    }

    /**
     * Registra un ingreso masivo de artículos.
     *
     * Recibe distribuciones por ubicación y crea un movimiento
     * por cada una. Actualiza el stock en cada ubicación.
     *
     * @param  StoreInputMovementRequest  $request  Petición validada con distribuciones.
     * @param  ProcessInputMovementAction  $action  Acción especializada en ingresos.
     */
    public function storeInput(
        StoreInputMovementRequest $request,
        ProcessInputMovementAction $action,
    ): JsonResponse {
        $data = new InputMovementData(
            itemId: (int) $request->validated('item_id'),
            userId: (int) auth()->id(),
            distributions: $request->validated('distributions'),
            notes: $request->validated('notes'),
        );

        $movements = $action->execute($data);
        $movements->each(fn ($movement) => $movement->load(['item', 'location', 'user']));

        return MovementResource::collection($movements)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Registra una salida de artículos del inventario.
     *
     * Valida stock suficiente antes de procesar la salida.
     *
     * @param  StoreOutputMovementRequest  $request  Petición validada con artículo, ubicación y cantidad.
     * @param  ProcessOutputMovementAction  $action  Acción especializada en salidas.
     */
    public function storeOutput(
        StoreOutputMovementRequest $request,
        ProcessOutputMovementAction $action,
    ): JsonResponse {
        $data = new MovementData(
            itemId: (int) $request->validated('item_id'),
            locationId: (int) $request->validated('location_id'),
            userId: (int) auth()->id(),
            type: MovementType::OUTPUT,
            quantity: (float) $request->validated('quantity'),
            notes: $request->validated('notes'),
        );

        $movement = $action->execute($data);
        $movement->load(['item', 'location', 'user']);

        return (new MovementResource($movement))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Registra una transferencia entre ubicaciones.
     *
     * Genera dos movimientos independientes: salida en origen
     * e ingreso en destino. Ambos en una transacción atómica.
     *
     * @param  StoreTransferMovementRequest  $request  Petición validada con origen, destino y cantidad.
     * @param  ProcessTransferMovementAction  $action  Acción especializada en transferencias.
     */
    public function storeTransfer(
        StoreTransferMovementRequest $request,
        ProcessTransferMovementAction $action,
    ): JsonResponse {
        $data = new TransferMovementData(
            itemId: (int) $request->validated('item_id'),
            userId: (int) auth()->id(),
            originLocationId: (int) $request->validated('origin_location_id'),
            destinationLocationId: (int) $request->validated('destination_location_id'),
            quantity: (float) $request->validated('quantity'),
            notes: $request->validated('notes'),
        );

        $result = $action->execute($data);

        return response()->json([
            'message' => 'Transferencia registrada exitosamente.',
            'data' => [
                'output' => new MovementResource($result['output']->load(['item', 'location', 'user'])),
                'input' => new MovementResource($result['input']->load(['item', 'location', 'user'])),
            ],
        ], 201);
    }

    /**
     * Registra un ajuste de inventario (reconteo).
     *
     * Calcula la diferencia entre stock registrado y conteo real,
     * y crea un movimiento de ajuste con la diferencia.
     *
     * @param  StoreAdjustmentMovementRequest  $request  Petición validada con cantidad contabilizada.
     * @param  ProcessAdjustmentMovementAction  $action  Acción especializada en ajustes.
     */
    public function storeAdjustment(
        StoreAdjustmentMovementRequest $request,
        ProcessAdjustmentMovementAction $action,
    ): JsonResponse {
        $data = new AdjustmentMovementData(
            itemId: (int) $request->validated('item_id'),
            userId: (int) auth()->id(),
            locationId: (int) $request->validated('location_id'),
            newQuantity: (float) $request->validated('new_quantity'),
            notes: $request->validated('notes'),
        );

        $movement = $action->execute($data);
        $movement->load(['item', 'location', 'user']);

        return (new MovementResource($movement))
            ->response()
            ->setStatusCode(201);
    }
}
