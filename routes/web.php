<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Empleado\DashboardController as EmpleadoDashboardController;
use App\Http\Controllers\Cliente\DashboardController as ClienteDashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PuestoController;
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

// Gestión de clientes (staff: superadmin + empleado)
Route::middleware(['auth', 'role:superadmin,empleado'])->group(function () {
    Route::resource('clientes', ClienteController::class);
});

// Gestión de empleados y puestos (dueño o empleado con puesto admin)
Route::middleware(['auth', 'gestiona.empleados'])->group(function () {
    Route::resource('puestos', PuestoController::class)->except('show');
    Route::resource('empleados', EmpleadoController::class);
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
