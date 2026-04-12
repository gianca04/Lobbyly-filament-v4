<?php

use App\Http\Controllers\InventoryApiController;
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
    });
