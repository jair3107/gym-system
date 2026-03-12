<?php

namespace App\Http\Controllers\Empleado;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('empleado.dashboard');
    }
}
