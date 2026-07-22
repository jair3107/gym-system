<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ventas (tickets) registradas en caja.
     */
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            // Cajero (usuario) que registró la venta.
            $table->foreignId('cajero_id')->constrained('users')->cascadeOnDelete();
            // Cliente al que se le vendió (opcional para venta de mostrador).
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->decimal('total', 10, 2)->default(0);
            $table->string('metodo_pago')->default('efectivo');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
