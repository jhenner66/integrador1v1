<?php

use App\Http\Controllers\Api\AlertaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\VentaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - D'Todo 
|--------------------------------------------------------------------------
*/

// ==========================================
// --- PÚBLICO (No requieren token) ---
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-cliente', [AuthController::class, 'loginCliente']);
Route::post('/registro-cliente', [AuthController::class, 'registroCliente']);


// ==========================================
// --- AUTENTICADO (Requieren token Sanctum) ---
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard (Pantalla 2)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Categorías
    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::post('/categorias', [CategoriaController::class, 'store'])->middleware('rol:administrador,almacen');
    Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])->middleware('rol:administrador,almacen');
    Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->middleware('rol:administrador');

    // Proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index']);
    Route::post('/proveedores', [ProveedorController::class, 'store'])->middleware('rol:administrador,almacen');
    Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])->middleware('rol:administrador,almacen');
    Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->middleware('rol:administrador');

    // Productos / Inventario (Pantalla 3)
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/codigo-barras/{codigoBarras}', [ProductoController::class, 'buscarPorCodigoBarras']);
    Route::get('/productos/{producto}', [ProductoController::class, 'show']);
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::put('/productos/{producto}', [ProductoController::class, 'update'])->middleware('rol:administrador,almacen');
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->middleware('rol:administrador');
    Route::post('/productos/{producto}/movimiento', [ProductoController::class, 'registrarMovimiento'])
        ->middleware('rol:administrador,almacen');
    Route::get('/movimientos', [App\Http\Controllers\Api\ProductoController::class, 'historialMovimientos']);

    // Pedidos a proveedores (Pantalla 4)
    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show']);
    Route::post('/pedidos', [PedidoController::class, 'store'])->middleware('rol:administrador,almacen');
    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'actualizarEstado'])
        ->middleware('rol:administrador,almacen');

    // Ventas
    Route::get('/ventas', [VentaController::class, 'index']);
    Route::get('/ventas/reporte', [VentaController::class, 'reporte']);
    Route::get('/ventas/exportar/excel', [VentaController::class, 'exportarExcel']);
    Route::get('/ventas/{venta}', [VentaController::class, 'show']);
    Route::post('/ventas', [VentaController::class, 'store']);

    // Alertas (Pantalla 2/3 - notificaciones)
    Route::get('/alertas', [AlertaController::class, 'index']);
    Route::patch('/alertas/{alerta}/atender', [AlertaController::class, 'marcarAtendida']);

    // Usuarios y roles (solo administrador)
    Route::apiResource('usuarios', UsuarioController::class)->except(['show'])
        ->middleware('rol:administrador');
});