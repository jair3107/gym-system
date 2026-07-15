<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEsNutriologo
{
    /**
     * Solo el dueño (superadmin) o un empleado con puesto "nutriologo" pueden
     * gestionar dietas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->puedeGestionarNutricion()) {
            abort(403, 'No tienes permiso para gestionar nutrición.');
        }

        return $next($request);
    }
}
