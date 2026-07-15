<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seguimientos_nutricion', function (Blueprint $table) {
            $table->id();
            // Nutriólogo responsable (usuario).
            $table->foreignId('nutriologo_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('dieta_id')->nullable()->constrained('dietas')->nullOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->decimal('peso_inicial', 5, 2)->nullable();
            $table->decimal('peso_meta', 5, 2)->nullable();
            $table->string('estado')->default('activo');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimientos_nutricion');
    }
};
