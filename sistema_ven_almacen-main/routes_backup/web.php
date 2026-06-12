<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Categorias;
use App\Http\Controllers\CierresCaja;
use App\Http\Controllers\Clientes;
use App\Http\Controllers\Combos;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\DetalleVentas;
use App\Http\Controllers\Empresas;
use App\Http\Controllers\Gastos;
use App\Http\Controllers\Inventario;
use App\Http\Controllers\Productos;
use App\Http\Controllers\Reportes;
use App\Http\Controllers\Usuarios;
use App\Http\Controllers\Ventas;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'index'])->name('login');
Route::post('/logear', [AuthController::class, 'logear'])->name('logear');

Route::middleware(['auth', 'rol'])->group(function () {
    Route::get('/home', [Dashboard::class, 'index'])->name('home');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Ventas (cajero + admin)
    Route::middleware('rol:admin,cajero')->group(function () {
        Route::prefix('ventas')->group(function () {
            Route::get('/nueva-venta', [Ventas::class, 'index'])->name('ventas-nueva');
            Route::post('/store', [Ventas::class, 'store'])->name('ventas.store');
            Route::get('/pendientes', [Ventas::class, 'pendientes'])->name('ventas.pendientes');
            Route::post('/{id}/agregar-items', [Ventas::class, 'agregarItems'])->name('ventas.agregar-items');
            Route::post('/{id}/cobrar', [Ventas::class, 'cobrar'])->name('ventas.cobrar');
            Route::post('/{id}/anular', [Ventas::class, 'anular'])->name('ventas.anular');
            Route::get('/ticket/{id}', [Ventas::class, 'ticket'])->name('ventas.ticket');
            Route::get('/ticket/{id}/doble', [Ventas::class, 'ticketDoble'])->name('ventas.ticket.doble');
            Route::get('/ticket/{id}/pdf', [Ventas::class, 'ticketPdf'])->name('ventas.ticket.pdf');
        });

        Route::prefix('detalle')->group(function () {
            Route::get('/detalle-venta', [DetalleVentas::class, 'index'])->name('detalle-venta');
            Route::get('/{id}', [DetalleVentas::class, 'show'])->name('detalle-venta.show');
        });

        // Cierres de caja: cajero puede abrir/cerrar el suyo
        Route::prefix('cierres')->group(function () {
            Route::get('/iniciar', [CierresCaja::class, 'iniciarForm'])->name('cierres.iniciar.form');
            Route::post('/iniciar', [CierresCaja::class, 'iniciar'])->name('cierres.iniciar');
            Route::get('/cerrar', [CierresCaja::class, 'cerrarForm'])->name('cierres.cerrar.form');
            Route::post('/cerrar', [CierresCaja::class, 'cerrar'])->name('cierres.cerrar');
        });

        // Gastos de caja (gastos urgentes): admin y cajero
        Route::prefix('gastos')->group(function () {
            Route::get('/', [Gastos::class, 'index'])->name('gastos');
            Route::get('/create', [Gastos::class, 'create'])->name('gastos.create');
            Route::post('/store', [Gastos::class, 'store'])->name('gastos.store');
            Route::delete('/{id}', [Gastos::class, 'destroy'])->name('gastos.destroy');
        });
    });

    // Cierres: ver historial — admin/almacen ven todos, cajero solo los suyos
    Route::prefix('cierres')->group(function () {
        Route::get('/', [CierresCaja::class, 'index'])->name('cierres');
        Route::get('/{id}', [CierresCaja::class, 'show'])->name('cierres.show');
        Route::middleware('rol:admin,almacen')->group(function () {
            Route::get('/{id}/pdf', [CierresCaja::class, 'pdf'])->name('cierres.pdf');
        });
    });

    // Reportes: admin + almacen (almacen hace de contador)
    Route::middleware('rol:admin,almacen')->prefix('reportes')->group(function () {
        Route::get('/', [Reportes::class, 'index'])->name('reportes');
        Route::get('/ventas-diarias', [Reportes::class, 'ventasDiarias'])->name('reportes.ventas-diarias');
        Route::get('/ventas-rango', [Reportes::class, 'ventasRango'])->name('reportes.ventas-rango');
        Route::get('/productos-vendidos', [Reportes::class, 'productosVendidos'])->name('reportes.productos-vendidos');
        Route::get('/stock-bajo', [Reportes::class, 'stockBajo'])->name('reportes.stock-bajo');
        Route::get('/ingresos-dia', [Reportes::class, 'ingresosDia'])->name('reportes.ingresos-dia');
        Route::get('/cierres', [Reportes::class, 'cierres'])->name('reportes.cierres');
        Route::get('/pdf/{tipo}', [Reportes::class, 'pdf'])->name('reportes.pdf');
    });

    // Categorías y productos: admin + almacen
    Route::middleware('rol:admin,almacen')->group(function () {
        Route::prefix('categorias')->group(function () {
            Route::get('/', [Categorias::class, 'index'])->name('categorias');
            Route::get('/create', [Categorias::class, 'create'])->name('categorias.create');
            Route::post('/store', [Categorias::class, 'store'])->name('categorias.store');
            Route::get('/show/{id}', [Categorias::class, 'show'])->name('categorias.show');
            Route::delete('/destroy/{id}', [Categorias::class, 'destroy'])->name('categorias.destroy');
            Route::get('/edit/{id}', [Categorias::class, 'edit'])->name('categorias.edit');
            Route::put('/update/{id}', [Categorias::class, 'update'])->name('categorias.update');
        });

        Route::prefix('productos')->group(function () {
            Route::get('/', [Productos::class, 'index'])->name('productos');
            Route::get('/create', [Productos::class, 'create'])->name('productos.create');
            Route::post('/store', [Productos::class, 'store'])->name('productos.store');
            Route::get('/edit/{id}', [Productos::class, 'edit'])->name('productos.edit');
            Route::put('/update/{id}', [Productos::class, 'update'])->name('productos.update');
            Route::get('/show-image/{id}', [Productos::class, 'show_image'])->name('productos.show.image');
            Route::put('/update-image/{id}', [Productos::class, 'update_image'])->name('productos.update.image');
            Route::get('/show/{id}', [Productos::class, 'show'])->name('productos.show');
            Route::delete('/destroy/{id}', [Productos::class, 'destroy'])->name('productos.destroy');
            Route::patch('/cambiar-estado/{id}', [Productos::class, 'estado'])->name('productos.estado');
        });

        Route::prefix('combos')->group(function () {
            Route::get('/', [Combos::class, 'index'])->name('combos');
            Route::get('/create', [Combos::class, 'create'])->name('combos.create');
            Route::post('/store', [Combos::class, 'store'])->name('combos.store');
            Route::get('/edit/{id}', [Combos::class, 'edit'])->name('combos.edit');
            Route::put('/update/{id}', [Combos::class, 'update'])->name('combos.update');
            Route::delete('/destroy/{id}', [Combos::class, 'destroy'])->name('combos.destroy');
            Route::patch('/toggle/{id}', [Combos::class, 'toggleEstado'])->name('combos.toggle');
        });

        Route::prefix('inventario')->group(function () {
            Route::get('/', [Inventario::class, 'index'])->name('inventario');
            Route::get('/create', [Inventario::class, 'create'])->name('inventario.create');
            Route::post('/store', [Inventario::class, 'store'])->name('inventario.store');
            Route::get('/stock-bajo', [Inventario::class, 'stockBajo'])->name('inventario.stock-bajo');
        });
    });

    // Clientes: cualquier rol autenticado puede listarlos / crearlos
    Route::prefix('clientes')->group(function () {
        Route::get('/', [Clientes::class, 'index'])->name('clientes');
        Route::get('/create', [Clientes::class, 'create'])->name('clientes.create');
        Route::post('/store', [Clientes::class, 'store'])->name('clientes.store');
        Route::get('/edit/{id}', [Clientes::class, 'edit'])->name('clientes.edit');
        Route::put('/update/{id}', [Clientes::class, 'update'])->name('clientes.update');
        Route::delete('/destroy/{id}', [Clientes::class, 'destroy'])->name('clientes.destroy');
    });

    // Empresa: solo admin
    Route::middleware('rol:admin')->group(function () {
        Route::get('/empresa', [Empresas::class, 'edit'])->name('empresa.edit');
        Route::put('/empresa', [Empresas::class, 'update'])->name('empresa.update');
    });

    // Usuarios: solo admin
    Route::middleware('rol:admin')->prefix('usuarios')->group(function () {
        Route::get('/', [Usuarios::class, 'index'])->name('usuarios');
        Route::get('/create', [Usuarios::class, 'create'])->name('usuarios.create');
        Route::post('/store', [Usuarios::class, 'store'])->name('usuarios.store');
        Route::get('/edit/{id}', [Usuarios::class, 'edit'])->name('usuarios.edit');
        Route::put('/update/{id}', [Usuarios::class, 'update'])->name('usuarios.update');
        Route::get('/tbody', [Usuarios::class, 'tbody'])->name('usuarios.tbody');
        Route::patch('/cambiar-estado/{id}', [Usuarios::class, 'estado'])->name('usuarios.estado');
        Route::patch('/cambiar-password/{id}', [Usuarios::class, 'cambio_password'])->name('usuarios.password');
    });
});
