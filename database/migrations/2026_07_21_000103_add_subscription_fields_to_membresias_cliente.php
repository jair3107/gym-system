<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Datos de suscripción sobre la vigencia: auto-renovación (autocobro),
     * método de pago recordado, control de recordatorio y encadenado de periodos.
     */
    public function up(): void
    {
        Schema::table('membresias_cliente', function (Blueprint $table) {
            $table->foreignId('cajero_id')->nullable()->after('venta_id')->constrained('users')->nullOnDelete();
            $table->boolean('auto_renovacion')->default(false)->after('estado');
            $table->string('metodo_pago')->nullable()->after('auto_renovacion');
            $table->timestamp('recordatorio_enviado_at')->nullable()->after('metodo_pago');
            // Periodo anterior del que proviene esta renovación (encadenado).
            $table->unsignedBigInteger('renovacion_de_id')->nullable()->after('recordatorio_enviado_at');
        });
    }

    public function down(): void
    {
        Schema::table('membresias_cliente', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cajero_id');
            $table->dropColumn(['auto_renovacion', 'metodo_pago', 'recordatorio_enviado_at', 'renovacion_de_id']);
        });
    }
};
