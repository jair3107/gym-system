<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManageClientes
{
    /**
     * Solo el dueño (superadmin) o un empleado con puesto "admin" o "cajero"
     * (recepción) pueden dar de alta, editar o eliminar clientes. El entrenador
     * y el nutriólogo solo pueden consultar y autoasignarse.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->puedeGestionarClientes()) {
            abort(403, 'No tienes permiso para gestionar clientes.');
        }

        return $next($request);
    }
}
