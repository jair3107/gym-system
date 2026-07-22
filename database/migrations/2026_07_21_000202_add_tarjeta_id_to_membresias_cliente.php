<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tarjeta con la que se hará el autocobro de la suscripción (la primaria
     * del cliente al momento de activar la auto-renovación).
     */
    public function up(): void
    {
        Schema::table('membresias_cliente', function (Blueprint $table) {
            $table->foreignId('tarjeta_id')->nullable()->after('metodo_pago')->constrained('tarjetas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('membresias_cliente', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tarjeta_id');
        });
    }
};
