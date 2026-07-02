<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TesoreriaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| SAFA DIGITAL — Rutas Web
|--------------------------------------------------------------------------
*/

// Rutas de Autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirigir la raíz al tablero principal (requiere auth)
Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

// Rutas públicas de consulta
Route::get('/pedidos/track/{numero_orden}', [PedidoController::class, 'track'])->name('pedidos.track');
Route::get('/pagos/{id}/ticket', [PosController::class, 'descargarTicketPago'])->name('pago.ticket');

// Rutas Protegidas por Autenticación
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─── Pedidos (Kanban) ─────────────────────────────────────────────────────────
    Route::prefix('pedidos')->name('pedidos.')->middleware('permiso:pedidos')->group(function () {
        Route::get('/',              [PedidoController::class, 'index'])->name('index');
        Route::post('/',             [PedidoController::class, 'store'])->name('store');
        Route::patch('/{id}/estado', [PedidoController::class, 'updateEstado'])->name('updateEstado');
        Route::delete('/{id}',       [PedidoController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/ticket',   [PedidoController::class, 'descargarTicket'])->name('ticket');
        Route::get('/{id}/a4',       [PedidoController::class, 'descargarA4'])->name('a4');
        
        // Actualizar Fecha de Entrega y Archivos
        Route::patch('/{id}/fecha-entrega', [PedidoController::class, 'updateFechaEntrega'])->name('updateFechaEntrega');
        Route::post('/{id}/archivos',        [PedidoController::class, 'uploadFiles'])->name('uploadFiles');
        Route::post('/{id}/cancelar',        [PedidoController::class, 'cancelar'])->name('cancelar');
    });

    // ─── Configuración Global ──────────────────────────────────────────────────
    Route::prefix('configuracion')->name('configuracion.')->middleware('permiso:configuracion')->group(function () {
        Route::get('/', [\App\Http\Controllers\ConfiguracionController::class, 'index'])->name('index');
        Route::post('/empresa', [\App\Http\Controllers\ConfiguracionController::class, 'updateEmpresa'])->name('update.empresa');
        Route::post('/tickets', [\App\Http\Controllers\ConfiguracionController::class, 'updateTickets'])->name('update.tickets');
        Route::post('/whatsapp', [\App\Http\Controllers\ConfiguracionController::class, 'updateWhatsapp'])->name('update.whatsapp');
        Route::post('/reset-pruebas', [\App\Http\Controllers\ConfiguracionController::class, 'resetPruebas'])->name('reset.pruebas');

        // CRUD Usuarios
        Route::post('/usuarios', [\App\Http\Controllers\UsuarioController::class, 'store'])->name('usuarios.store');
        Route::patch('/usuarios/{id}', [\App\Http\Controllers\UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{id}', [\App\Http\Controllers\UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    });

    // ─── Clientes ─────────────────────────────────────────────────────────────────
    Route::prefix('clientes')->name('clientes.')->middleware('permiso:clientes')->group(function () {
        Route::get('/',  [ClienteController::class, 'index'])->name('index');
        Route::post('/', [ClienteController::class, 'store'])->name('store');
        Route::post('/quick', [ClienteController::class, 'quickStore'])->name('quickStore');
    });

    // ─── Tesorería / Caja ─────────────────────────────────────────────────────────
    Route::prefix('caja')->name('caja.')->middleware('permiso:caja')->group(function () {
        Route::get('/',  [CajaController::class, 'index'])->name('index');
        Route::post('/', [CajaController::class, 'store'])->name('store');
        Route::get('/{id}/ticket', [CajaController::class, 'descargarTicket'])->name('ticket');
        Route::get('/historial', [\App\Http\Controllers\HistorialMovimientosController::class, 'index'])->name('historial');
        Route::get('/historial/{id}/reimprimir', [\App\Http\Controllers\HistorialMovimientosController::class, 'reimprimir'])->name('historial.reimprimir');
    });

    // ─── Inventario ───────────────────────────────────────────────────────────────
    Route::prefix('inventario')->name('inventario.')->middleware('permiso:inventario')->group(function () {
        Route::get('/', [InventarioController::class, 'index'])->name('index');

        // Productos (Blanks)
        Route::post('/productos',        [InventarioController::class, 'storeProducto'])->name('storeProducto');
        Route::patch('/productos/{id}',  [InventarioController::class, 'updateProducto'])->name('updateProducto');
        Route::delete('/productos/{id}', [InventarioController::class, 'destroyProducto'])->name('destroyProducto');

        // Variantes
        Route::post('/variantes',         [InventarioController::class, 'storeVariante'])->name('storeVariante');
        Route::patch('/variantes/{id}',   [InventarioController::class, 'updateVariante'])->name('updateVariante');
        Route::delete('/variantes/{id}',  [InventarioController::class, 'destroyVariante'])->name('destroyVariante');
        Route::patch('/variantes/{id}/stock', [InventarioController::class, 'ajustarStock'])->name('ajustarStock');

        // Categorías
        Route::post('/categorias', [InventarioController::class, 'storeCategorias'])->name('storeCategorias');

        // Importación de Excel
        Route::post('/importar-excel', [InventarioController::class, 'importExcel'])->name('importarExcel');

        // AJAX: SKU sugerido y subida de imágenes
        Route::get('/sku-sugerido', [InventarioController::class, 'skuSugerido'])->name('skuSugerido');
        Route::post('/upload-imagen', [InventarioController::class, 'uploadImagen'])->name('uploadImagen');
    });

    // ─── POS (Punto de Venta) ─────────────────────────────────────────────────────
    Route::prefix('pos')->name('pos.')->middleware('permiso:pos')->group(function () {
        Route::get('/',                 [PosController::class, 'index'])->name('index');
        Route::post('/sesion/abrir',    [PosController::class, 'abrirSesion'])->name('abrirSesion');
        Route::get('/productos/buscar', [PosController::class, 'buscarProductos'])->name('buscarProductos');
        Route::post('/venta',           [PosController::class, 'procesarVenta'])->name('procesarVenta');
        Route::get('/venta/{id}/ticket', [PosController::class, 'ticketVenta'])->name('ticket');
        
        // Integración Pedidos
        Route::get('/pedidos/buscar',   [PosController::class, 'buscarPedido'])->name('buscarPedido');
        Route::post('/pedidos/pagar',   [PosController::class, 'pagarPedido'])->name('pagarPedido');
        
        Route::get('/corte',            [PosController::class, 'corteCaja'])->name('corteCaja');
        Route::post('/sesion/cerrar',   [PosController::class, 'cerrarSesion'])->name('cerrarSesion');
        Route::post('/sesion/corte',    [PosController::class, 'cerrarCaja'])->name('cerrarCaja');
    });

    // ─── Cotizaciones ─────────────────────────────────────────────────────────────
    Route::middleware('permiso:cotizaciones')->group(function () {
        Route::get('/cotizaciones',           [CotizacionController::class, 'index'])->name('cotizaciones.index');
        Route::post('/cotizaciones',          [CotizacionController::class, 'store'])->name('cotizaciones.store');
        Route::patch('/cotizaciones/{id}/estado', [CotizacionController::class, 'updateEstado'])->name('cotizaciones.updateEstado');
        Route::get('/cotizaciones/{id}/pdf',  [CotizacionController::class, 'descargarPDF'])->name('cotizaciones.pdf');
    });

    // ─── Compras (Órdenes de Compra) ──────────────────────────────────────────────
    Route::prefix('compras')->name('compras.')->middleware('permiso:compras')->group(function () {
        Route::get('/', [\App\Http\Controllers\CompraController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\CompraController::class, 'store'])->name('store');
        Route::post('/proveedores/quick', [\App\Http\Controllers\CompraController::class, 'quickStoreProveedor'])->name('proveedores.quickStore');
        Route::post('/{id}/recibir', [\App\Http\Controllers\CompraController::class, 'recibir'])->name('recibir');
        Route::post('/{id}/valorar', [\App\Http\Controllers\CompraController::class, 'valorar'])->name('valorar');
        Route::put('/{id}', [\App\Http\Controllers\CompraController::class, 'update'])->name('update');
        Route::get('/{id}/pdf', [\App\Http\Controllers\CompraController::class, 'descargarPDF'])->name('pdf');
    });

    // ─── Tesorería y Finanzas ──────────────────────────────────────────────────────
    Route::prefix('tesoreria')->name('tesoreria.')->middleware('permiso:caja')->group(function () {
        Route::get('/', [TesoreriaController::class, 'index'])->name('index');
        Route::post('/movimiento', [TesoreriaController::class, 'registrarMovimiento'])->name('movimiento');
        Route::post('/traslado', [TesoreriaController::class, 'trasladarFondos'])->name('traslado');
    });

    // ─── Reportes y Auditoría ──────────────────────────────────────────────────────
    Route::prefix('reportes')->name('reportes.')->middleware('permiso:reportes')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/cortes-caja', [ReporteController::class, 'cortesCaja'])->name('cortes');
        Route::get('/cortes-caja/{id}/ticket', [ReporteController::class, 'imprimirTicketCorte'])->name('corte.ticket');
        Route::get('/cortes-caja/{id}/pdf', [ReporteController::class, 'descargarA4Corte'])->name('corte.pdf');
        
        // Exportaciones PDF
        Route::get('/ventas/pdf', [ReporteController::class, 'ventasPdf'])->name('ventas.pdf');
        Route::get('/top-productos/pdf', [ReporteController::class, 'topProductosPdf'])->name('top-productos.pdf');
        Route::get('/rentabilidad/pdf', [ReporteController::class, 'rentabilidadPdf'])->name('rentabilidad.pdf');
        Route::get('/flujo-tesoreria/pdf', [ReporteController::class, 'flujoTesoreriaPdf'])->name('flujo-tesoreria.pdf');
        Route::get('/compras/pdf', [ReporteController::class, 'comprasPdf'])->name('compras.pdf');
        Route::get('/ajustes-stock/pdf', [ReporteController::class, 'ajustesStockPdf'])->name('ajustes-stock.pdf');
    });

    Route::post('/notificaciones/leer-todas', function() {
        if (auth()->check()) {
            auth()->user()->unreadNotifications->markAsRead();
        }
        return back()->with('success', 'Todas las notificaciones marcadas como leídas.');
    })->name('notificaciones.leerTodas');

});
