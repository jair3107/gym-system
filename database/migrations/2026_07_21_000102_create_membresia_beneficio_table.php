<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Productos/servicios incluidos como beneficio de un plan de membresía.
     * El beneficiable es polimórfico (producto o servicio).
     */
    public function up(): void
    {
        Schema::create('membresia_beneficio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membresia_id')->constrained('membresias')->cascadeOnDelete();
            $table->nullableMorphs('beneficiable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membresia_beneficio');
    }
};
