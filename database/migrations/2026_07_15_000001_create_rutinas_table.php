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
        Schema::create('rutinas', function (Blueprint $table) {
            $table->id();
            // Entrenador (usuario) dueño de la rutina.
            $table->foreignId('entrenador_id')->constrained('users')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('objetivo')->nullable();
            $table->string('nivel')->nullable();
            $table->unsignedInteger('duracion_min')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            // Plan semanal: { duraciones: {bloque: texto}, dias: {dia: {bloque: contenido}} }
            $table->json('plan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutinas');
    }
};
