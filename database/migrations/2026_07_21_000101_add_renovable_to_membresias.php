<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ¿El plan admite auto-renovación (suscripción con autocobro)?
     */
    public function up(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            $table->boolean('renovable')->default(true)->after('activa');
        });
    }

    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            $table->dropColumn('renovable');
        });
    }
};
