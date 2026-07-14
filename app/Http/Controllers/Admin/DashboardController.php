<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClienteEstado;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalClientes' => Cliente::count(),
            'clientesActivos' => Cliente::where('estado', ClienteEstado::Activo)->count(),
            'clientesSuspendidos' => Cliente::where('estado', ClienteEstado::Suspendido)->count(),
            'totalEmpleados' => User::where('role', UserRole::Empleado)->count(),
            'ultimosClientes' => Cliente::with('user')->latest()->take(5)->get(),
        ]);
    }
}
