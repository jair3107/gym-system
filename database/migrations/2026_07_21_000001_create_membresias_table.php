<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo de planes de membresía que el cajero puede vender.
     */
    public function up(): void
    {
        Schema::create('membresias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            // Vigencia que otorga la membresía al venderse (en días).
            $table->unsignedInteger('duracion_dias');
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membresias');
    }
};
