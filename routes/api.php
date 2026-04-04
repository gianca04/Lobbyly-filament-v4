<?php

use App\Http\Controllers\MovementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Rutas de Movimientos de Inventario
|--------------------------------------------------------------------------
|
| Endpoints RESTful para gestionar los movimientos de inventario.
| Cada tipo de movimiento tiene su propio endpoint para mantener
| la separación de responsabilidades.
|
*/
Route::prefix('movements')->name('movements.')->group(function (): void {
    Route::get('/', [MovementController::class, 'index'])->name('index');
    Route::get('/{movement}', [MovementController::class, 'show'])->name('show');

    Route::post('/input', [MovementController::class, 'storeInput'])->name('store.input');
    Route::post('/output', [MovementController::class, 'storeOutput'])->name('store.output');
    Route::post('/transfer', [MovementController::class, 'storeTransfer'])->name('store.transfer');
    Route::post('/adjustment', [MovementController::class, 'storeAdjustment'])->name('store.adjustment');
});
