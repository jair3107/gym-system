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
        Schema::create('mediciones_nutricion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seguimiento_nutricion_id')->constrained('seguimientos_nutricion')->cascadeOnDelete();
            $table->date('fecha');
            $table->decimal('peso', 5, 2);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mediciones_nutricion');
    }
};
