<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tarjetas de crédito/débito guardadas por cliente.
     *
     * IMPORTANTE: no se almacena el número completo ni el CVV (sería un problema
     * de seguridad/PCI). Solo se guardan datos no sensibles para identificarla:
     * titular, marca, tipo, últimos 4 dígitos y vencimiento.
     */
    public function up(): void
    {
        Schema::create('tarjetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('titular');
            $table->string('marca');           // visa, mastercard, amex, discover, otra
            $table->string('tipo');            // credito | debito
            $table->char('ultimos4', 4);
            $table->unsignedTinyInteger('mes_exp');
            $table->unsignedSmallInteger('anio_exp');
            $table->boolean('primaria')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarjetas');
    }
};
