<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('cliente.dashboard');
    }
}
