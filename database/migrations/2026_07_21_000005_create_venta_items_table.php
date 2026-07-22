<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Líneas de cada venta. El vendible (membresía/producto/servicio) es
     * polimórfico y nullable, para conservar el histórico aunque se elimine
     * el elemento del catálogo. El nombre y el precio se guardan como copia.
     */
    public function up(): void
    {
        Schema::create('venta_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->nullableMorphs('vendible');
            $table->string('descripcion');
            $table->decimal('precio_unitario', 10, 2);
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_items');
    }
};
