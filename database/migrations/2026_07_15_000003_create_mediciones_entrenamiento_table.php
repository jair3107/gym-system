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
        Schema::create('mediciones_entrenamiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seguimiento_entrenamiento_id')->constrained('seguimientos_entrenamiento')->cascadeOnDelete();
            $table->date('fecha');
            $table->decimal('peso', 5, 2);
            $table->decimal('masa_muscular', 5, 2)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mediciones_entrenamiento');
    }
};
