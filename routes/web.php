<?php

use App\Http\Controllers\InventoryApiController;
use App\Http\Controllers\MovementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Rutas internas de consulta de inventario
|--------------------------------------------------------------------------
|
| Endpoints JSON consumidos por la vista Blade de movimientos.
| Usan el middleware 'auth' (sesión web) para aprovechar la
| autenticación del panel Filament sin necesidad de tokens API.
|
*/
Route::prefix('internal/inventory')
    ->middleware('auth')
    ->name('internal.inventory.')
    ->group(function (): void {
        Route::get('/items', [InventoryApiController::class, 'items'])->name('items');
        Route::get('/locations', [InventoryApiController::class, 'locations'])->name('locations');
        Route::get('/stock', [InventoryApiController::class, 'stock'])->name('stock');
        Route::get('/item-locations', [InventoryApiController::class, 'itemLocations'])->name('item-locations');
        Route::get('/all-locations-stock', [InventoryApiController::class, 'allLocationsWithStock'])->name('all-locations-stock');
    });

/*
|--------------------------------------------------------------------------
| Rutas internas de registro de movimientos
|--------------------------------------------------------------------------
|
| Endpoints para procesar los movimientos desde la vista unificada.
| Usan el middleware 'auth/web' para obtener el usuario autenticado.
|
*/
Route::prefix('internal/movements')
    ->middleware('auth')
    ->name('internal.movements.')
    ->group(function (): void {
        Route::get('/', [MovementController::class, 'index'])->name('index');
        Route::get('/{movement}', [MovementController::class, 'show'])->name('show');

        Route::post('/input', [MovementController::class, 'storeInput'])->name('store.input');
        Route::post('/output', [MovementController::class, 'storeOutput'])->name('store.output');
        Route::post('/transfer', [MovementController::class, 'storeTransfer'])->name('store.transfer');
        Route::post('/adjustment', [MovementController::class, 'storeAdjustment'])->name('store.adjustment');

        Route::post('/batch-transfer', [MovementController::class, 'storeBatchTransfer'])->name('store.batch-transfer');
        Route::post('/batch-adjustment', [MovementController::class, 'storeBatchAdjustment'])->name('store.batch-adjustment');
    });
