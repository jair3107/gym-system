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
        Schema::create('dietas', function (Blueprint $table) {
            $table->id();
            // Nutriólogo (usuario) dueño de la dieta.
            $table->foreignId('nutriologo_id')->constrained('users')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('objetivo')->nullable();
            $table->unsignedInteger('calorias_objetivo')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            // Plan semanal: { horarios: {tiempo: hora}, dias: {dia: {tiempo: contenido}} }
            $table->json('plan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dietas');
    }
};
