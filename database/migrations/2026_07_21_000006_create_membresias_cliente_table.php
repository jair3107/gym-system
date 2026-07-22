<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membresías vendidas a un cliente, con su vigencia. La membresía vigente
     * es la que determina si el cliente está activo.
     */
    public function up(): void
    {
        Schema::create('membresias_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('membresia_id')->nullable()->constrained('membresias')->nullOnDelete();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado')->default('activa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membresias_cliente');
    }
};
