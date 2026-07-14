<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManageEmpleados
{
    /**
     * Solo el dueño (superadmin) o un empleado con puesto "admin" pueden
     * gestionar empleados. El resto no puede ver ni tocar esta sección.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->puedeGestionarEmpleados()) {
            abort(403, 'No tienes permiso para gestionar empleados.');
        }

        return $next($request);
    }
}
