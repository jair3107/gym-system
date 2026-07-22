<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro de entregas (canjes) de un beneficio incluido en la membresía,
     * mientras la suscripción está activa. Los productos descuentan inventario.
     */
    public function up(): void
    {
        Schema::create('beneficio_canjes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membresia_cliente_id')->constrained('membresias_cliente')->cascadeOnDelete();
            $table->nullableMorphs('beneficiable');
            $table->string('descripcion');
            $table->unsignedInteger('cantidad')->default(1);
            $table->foreignId('atendido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nota')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficio_canjes');
    }
};
