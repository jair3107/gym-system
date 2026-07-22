<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePuedeGestionarMembresias
{
    /**
     * Solo el dueño (superadmin) o un empleado con puesto "admin" o "cajero"
     * pueden gestionar el catálogo de planes de membresía.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->puedeGestionarMembresias()) {
            abort(403, 'No tienes permiso para gestionar membresías.');
        }

        return $next($request);
    }
}
