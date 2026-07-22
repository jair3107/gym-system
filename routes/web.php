<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Empleado\DashboardController as EmpleadoDashboardController;
use App\Http\Controllers\Cliente\DashboardController as ClienteDashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DietaController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\MedicionEntrenamientoController;
use App\Http\Controllers\MedicionNutricionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\RutinaController;
use App\Http\Controllers\SeguimientoEntrenamientoController;
use App\Http\Controllers\SeguimientoNutricionController;
use App\Http\Controllers\BeneficioCanjeController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\SuscripcionController;
use App\Http\Controllers\TarjetaController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Admin routes
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});

// Empleado routes
Route::middleware(['auth', 'role:superadmin,empleado'])->prefix('empleado')->name('empleado.')->group(function () {
    Route::get('/dashboard', [EmpleadoDashboardController::class, 'index'])->name('dashboard');
});

// Clientes (staff: superadmin + empleado)
Route::middleware(['auth', 'role:superadmin,empleado'])->group(function () {
    // Gestión completa (alta/edición/eliminación): solo superadmin, admin o cajero.
    Route::middleware('gestiona.clientes')->group(function () {
        Route::get('clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
        Route::post('clientes', [ClienteController::class, 'store'])->name('clientes.store');
        Route::get('clientes/{cliente}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');
        Route::match(['put', 'patch'], 'clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
        Route::delete('clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    });

    // Consulta y autoasignación: cualquier staff (el listado va filtrado por rol).
    Route::get('clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::post('clientes/{cliente}/autoasignar', [ClienteController::class, 'autoasignar'])->name('clientes.autoasignar');
});

// Gestión de empleados y puestos (dueño o empleado con puesto admin)
Route::middleware(['auth', 'gestiona.empleados'])->group(function () {
    Route::resource('puestos', PuestoController::class)->except('show');
    Route::resource('empleados', EmpleadoController::class);
});

// Nutrición: dietas y seguimientos (dueño o empleado con puesto nutriólogo)
Route::middleware(['auth', 'gestiona.nutricion'])->group(function () {
    Route::resource('dietas', DietaController::class);

    Route::resource('seguimientos-nutricion', SeguimientoNutricionController::class)
        ->parameters(['seguimientos-nutricion' => 'seguimiento']);

    // Mediciones de progreso, anidadas al seguimiento.
    Route::post('seguimientos-nutricion/{seguimiento}/mediciones', [MedicionNutricionController::class, 'store'])
        ->name('seguimientos-nutricion.mediciones.store');
    Route::delete('mediciones-nutricion/{medicion}', [MedicionNutricionController::class, 'destroy'])
        ->name('mediciones-nutricion.destroy');

    // Asignar una dieta a un paciente desde su ficha (crea el seguimiento).
    Route::post('clientes/{cliente}/asignar-dieta', [SeguimientoNutricionController::class, 'asignarDieta'])
        ->name('seguimientos-nutricion.asignar');
});

// Entrenamiento: rutinas y seguimientos (dueño o empleado con puesto entrenador)
Route::middleware(['auth', 'gestiona.entrenamiento'])->group(function () {
    Route::resource('rutinas', RutinaController::class);

    Route::resource('seguimientos-entrenamiento', SeguimientoEntrenamientoController::class)
        ->parameters(['seguimientos-entrenamiento' => 'seguimiento']);

    // Mediciones de progreso, anidadas al seguimiento.
    Route::post('seguimientos-entrenamiento/{seguimiento}/mediciones', [MedicionEntrenamientoController::class, 'store'])
        ->name('seguimientos-entrenamiento.mediciones.store');
    Route::delete('mediciones-entrenamiento/{medicion}', [MedicionEntrenamientoController::class, 'destroy'])
        ->name('mediciones-entrenamiento.destroy');

    // Asignar una rutina a un alumno desde su ficha (crea el seguimiento).
    Route::post('clientes/{cliente}/asignar-rutina', [SeguimientoEntrenamientoController::class, 'asignarRutina'])
        ->name('seguimientos-entrenamiento.asignar');
});

// Catálogo de planes de membresía (dueño, admin o cajero)
Route::middleware(['auth', 'gestiona.membresias'])->group(function () {
    Route::resource('membresias', MembresiaController::class)->except('show')
        ->parameters(['membresias' => 'membresia']);
});

// Caja/Ventas: catálogos y registro de ventas (dueño o empleado con puesto cajero)
Route::middleware(['auth', 'gestiona.ventas'])->group(function () {
    Route::resource('productos', ProductoController::class)->except('show');
    Route::resource('servicios', ServicioController::class)->except('show');

    // Registro de ventas (no se editan una vez emitidas).
    Route::resource('ventas', VentaController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // Entrega (canje) de un beneficio incluido en una suscripción activa.
    Route::post('membresias-cliente/{membresiaCliente}/canjes', [BeneficioCanjeController::class, 'store'])
        ->name('canjes.store');

    // Activar/desactivar la auto-renovación de una suscripción.
    Route::patch('membresias-cliente/{membresiaCliente}/auto-renovacion', [SuscripcionController::class, 'toggleAutoRenovacion'])
        ->name('suscripciones.auto-renovacion');

    // Métodos de pago (tarjetas) del cliente.
    Route::post('clientes/{cliente}/tarjetas', [TarjetaController::class, 'store'])->name('tarjetas.store');
    Route::patch('tarjetas/{tarjeta}/primaria', [TarjetaController::class, 'primaria'])->name('tarjetas.primaria');
    Route::delete('tarjetas/{tarjeta}', [TarjetaController::class, 'destroy'])->name('tarjetas.destroy');
});

// Centro de notificaciones (cualquier usuario autenticado)
Route::middleware('auth')->group(function () {
    Route::get('notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::patch('notificaciones/{id}/leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leida');
    Route::patch('notificaciones/leer-todas', [NotificacionController::class, 'marcarTodas'])->name('notificaciones.leer-todas');
});

// Cliente routes
Route::middleware(['auth', 'role:superadmin,empleado,cliente'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/dashboard', [ClienteDashboardController::class, 'index'])->name('dashboard');
});

// Profile routes (all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
