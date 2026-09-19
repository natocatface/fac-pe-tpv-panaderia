<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComprobanteElectronicoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TpvController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

// Login
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // TPV - Punto de Venta
    Route::prefix('tpv')->name('tpv.')->group(function () {
        Route::get('/', [TpvController::class, 'index'])->name('index');
        Route::post('/venta', [TpvController::class, 'storeVenta'])->name('venta.store');
        Route::get('/productos/buscar', [TpvController::class, 'buscarProductos'])->name('productos.buscar');
        Route::get('/ticket/{venta}', [TpvController::class, 'imprimirTicket'])->name('ticket');
        Route::get('/sesion/abrir', [TpvController::class, 'abrirSesion'])->name('sesion.abrir');
        Route::post('/sesion/cerrar', [TpvController::class, 'cerrarSesion'])->name('sesion.cerrar');
    });

    // Productos y Categorías
    Route::resource('productos', ProductoController::class);
    Route::post('productos/{producto}/receta', [ProductoController::class, 'guardarReceta'])->name('productos.receta');
    Route::resource('categorias', CategoriaController::class);

    // Stock y Mermas
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/movimientos', [StockController::class, 'movimientos'])->name('movimientos');
        Route::post('/ajuste', [StockController::class, 'ajuste'])->name('ajuste');
        Route::get('/mermas', [StockController::class, 'mermas'])->name('mermas');
        Route::post('/merma', [StockController::class, 'storeMerma'])->name('mermas.store');
        Route::get('/alertas', [StockController::class, 'alertas'])->name('alertas');
    });

    // Clientes y Proveedores
    Route::resource('clientes', ClienteController::class);
    Route::resource('proveedores', ProveedorController::class);

    // Documentos (Tickets / Facturas / Presupuestos / Albaranes)
    Route::prefix('documentos')->name('documentos.')->group(function () {
        Route::get('/', [DocumentoController::class, 'index'])->name('index');
        Route::get('/tickets', [DocumentoController::class, 'tickets'])->name('tickets');
        Route::get('/facturas', [DocumentoController::class, 'facturas'])->name('facturas');
        Route::get('/presupuestos', [DocumentoController::class, 'presupuestos'])->name('presupuestos');
        Route::get('/albaranes', [DocumentoController::class, 'albaranes'])->name('albaranes');
        Route::get('/{tipo}/crear', [DocumentoController::class, 'crear'])->name('crear');
        Route::post('/{tipo}', [DocumentoController::class, 'store'])->name('store');
        Route::get('/{tipo}/{id}/pdf', [DocumentoController::class, 'pdf'])->name('pdf');
        Route::get('/{tipo}/{id}', [DocumentoController::class, 'show'])->name('show');
    });

    // Compras
    Route::resource('compras', CompraController::class);
    Route::post('compras/{compra}/recibir', [CompraController::class, 'recibir'])->name('compras.recibir');

    // Informes
    Route::prefix('informes')->name('informes.')->group(function () {
        Route::get('/', [InformeController::class, 'index'])->name('index');
        Route::get('/ventas', [InformeController::class, 'ventas'])->name('ventas');
        Route::get('/productos', [InformeController::class, 'productos'])->name('productos');
        Route::get('/margenes', [InformeController::class, 'margenes'])->name('margenes');
        Route::get('/clientes', [InformeController::class, 'clientes'])->name('clientes');
        Route::get('/stock', [InformeController::class, 'stock'])->name('stock');
        Route::get('/caja', [InformeController::class, 'caja'])->name('caja');
    });

    // Control Horario
    Route::prefix('horarios')->name('horarios.')->group(function () {
        Route::get('/', [HorarioController::class, 'index'])->name('index');
        Route::post('/fichar', [HorarioController::class, 'fichar'])->name('fichar');
        Route::get('/historico', [HorarioController::class, 'historico'])->name('historico');
        Route::get('/empleado/{usuario}', [HorarioController::class, 'empleado'])->name('empleado');
    });

    // Usuarios
    Route::resource('usuarios', UsuarioController::class);

    // Configuración
    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        Route::get('/', [ConfiguracionController::class, 'index'])->name('index');
        Route::post('/empresa', [ConfiguracionController::class, 'actualizarEmpresa'])->name('empresa');
        Route::post('/regional', [ConfiguracionController::class, 'actualizarRegional'])->name('regional');
        Route::post('/impuestos', [ConfiguracionController::class, 'actualizarImpuestos'])->name('impuestos');
        Route::post('/documentos', [ConfiguracionController::class, 'actualizarDocumentos'])->name('documentos');
        Route::post('/logo', [ConfiguracionController::class, 'subirLogo'])->name('logo');
        Route::post('/sunat', [ConfiguracionController::class, 'actualizarSunat'])->name('sunat');
        Route::post('/sunat/certificado', [ConfiguracionController::class, 'subirCertificado'])->name('sunat.certificado');
    });

    // SUNAT - Comprobantes electrónicos (Perú)
    Route::prefix('sunat')->name('sunat.')->group(function () {
        Route::get('/', [ComprobanteElectronicoController::class, 'index'])->name('index');
        Route::get('/{venta}', [ComprobanteElectronicoController::class, 'show'])->name('show');
        Route::post('/{venta}/emitir', [ComprobanteElectronicoController::class, 'emitir'])->name('emitir');
        Route::post('/{venta}/reintentar', [ComprobanteElectronicoController::class, 'reintentar'])->name('reintentar');
        Route::post('/{venta}/anular', [ComprobanteElectronicoController::class, 'anular'])->name('anular');
        Route::get('/{venta}/xml', [ComprobanteElectronicoController::class, 'descargarXml'])->name('xml');
        Route::get('/{venta}/cdr', [ComprobanteElectronicoController::class, 'descargarCdr'])->name('cdr');
        Route::get('/{venta}/pdf', [ComprobanteElectronicoController::class, 'pdf'])->name('pdf');
    });

    // Backup y Mantenimiento
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/crear', [BackupController::class, 'crear'])->name('crear');
        Route::get('/descargar/{archivo}', [BackupController::class, 'descargar'])->name('descargar');
        Route::delete('/eliminar/{archivo}', [BackupController::class, 'eliminar'])->name('eliminar');
        Route::post('/restaurar', [BackupController::class, 'restaurar'])->name('restaurar');
        Route::post('/reset', [BackupController::class, 'reset'])->name('reset');
    });
});
