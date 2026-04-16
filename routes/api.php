<?php

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
// Las rutas de movimientos se han movido a routes/web.php bajo el prefijo 'internal/movements'
// para asegurar el acceso a la sesión y al usuario autenticado.
