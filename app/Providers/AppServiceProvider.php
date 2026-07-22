<?php

namespace App\Providers;

use App\Models\Membresia;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Alias cortos para los tipos polimórficos (líneas de venta y beneficios).
        // No es estricto: otros modelos (p. ej. User en notificaciones) siguen
        // guardando su nombre de clase completo.
        Relation::morphMap([
            'membresia' => Membresia::class,
            'producto' => Producto::class,
            'servicio' => Servicio::class,
        ]);
    }
}
