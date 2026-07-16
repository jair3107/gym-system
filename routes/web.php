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
