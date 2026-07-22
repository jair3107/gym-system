<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca las ventas generadas por el autocobro de una renovación.
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->boolean('automatica')->default(false)->after('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('automatica');
        });
    }
};
