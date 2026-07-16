<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEsEntrenador
{
    /**
     * Solo el dueño (superadmin) o un empleado con puesto "entrenador" pueden
     * gestionar rutinas y seguimientos de entrenamiento.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->puedeGestionarEntrenamiento()) {
            abort(403, 'No tienes permiso para gestionar entrenamiento.');
        }

        return $next($request);
    }
}
