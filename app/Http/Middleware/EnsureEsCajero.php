<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEsCajero
{
    /**
     * Solo el dueño (superadmin) o un empleado con puesto "cajero" pueden
     * gestionar la caja: catálogos (membresías, productos, servicios) y ventas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->puedeGestionarVentas()) {
            abort(403, 'No tienes permiso para gestionar la caja.');
        }

        return $next($request);
    }
}
