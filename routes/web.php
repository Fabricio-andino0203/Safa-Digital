<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PosController;

/*
|--------------------------------------------------------------------------
| SAFA DIGITAL — Rutas Web
|--------------------------------------------------------------------------
*/

// Redirigir la raíz al tablero de pedidos
Route::get('/', function () {
    return redirect()->route('pedidos.index');
});

// ─── Pedidos (Kanban) ─────────────────────────────────────────────────────────
Route::prefix('pedidos')->name('pedidos.')->group(function () {
    Route::get('/',              [PedidoController::class, 'index'])->name('index');
    Route::post('/',             [PedidoController::class, 'store'])->name('store');
    Route::patch('/{id}/estado', [PedidoController::class, 'updateEstado'])->name('updateEstado');
});

// ─── Clientes ─────────────────────────────────────────────────────────────────
Route::prefix('clientes')->name('clientes.')->group(function () {
    Route::get('/',  [ClienteController::class, 'index'])->name('index');
    Route::post('/', [ClienteController::class, 'store'])->name('store');
});

// ─── Tesorería / Caja ─────────────────────────────────────────────────────────
Route::prefix('caja')->name('caja.')->group(function () {
    Route::get('/',  [CajaController::class, 'index'])->name('index');
    Route::post('/', [CajaController::class, 'store'])->name('store');
});

// ─── Inventario ───────────────────────────────────────────────────────────────
Route::prefix('inventario')->name('inventario.')->group(function () {
    Route::get('/',             [InventarioController::class, 'index'])->name('index');
    Route::patch('/{id}/stock', [InventarioController::class, 'updateStock'])->name('updateStock');
});

// ─── POS (Punto de Venta) ─────────────────────────────────────────────────────
Route::prefix('pos')->name('pos.')->group(function () {
    Route::get('/',                [PosController::class, 'index'])->name('index');
    Route::post('/sesion/abrir',   [PosController::class, 'abrirSesion'])->name('abrirSesion');
    Route::get('/productos/buscar',[PosController::class, 'buscarProductos'])->name('buscarProductos');
    Route::post('/venta',          [PosController::class, 'procesarVenta'])->name('procesarVenta');
    Route::get('/corte',           [PosController::class, 'corteCaja'])->name('corteCaja');
    Route::post('/sesion/cerrar',  [PosController::class, 'cerrarSesion'])->name('cerrarSesion');
});
