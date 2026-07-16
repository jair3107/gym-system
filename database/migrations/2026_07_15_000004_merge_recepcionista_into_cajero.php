<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Unifica el puesto "recepcionista" dentro de "cajero": reasigna sus
     * empleados al puesto cajero (sin duplicar) y elimina el puesto sobrante.
     * El cajero pasa a llamarse "Recepcionista / Cajero".
     */
    public function up(): void
    {
        $cajeroId = DB::table('puestos')->where('slug', 'cajero')->value('id');
        $recepId = DB::table('puestos')->where('slug', 'recepcionista')->value('id');

        if ($recepId && $cajeroId) {
            $usuarios = DB::table('empleado_puesto')->where('puesto_id', $recepId)->pluck('user_id');

            foreach ($usuarios as $userId) {
                $yaEsCajero = DB::table('empleado_puesto')
                    ->where('puesto_id', $cajeroId)
                    ->where('user_id', $userId)
                    ->exists();

                if ($yaEsCajero) {
                    DB::table('empleado_puesto')
                        ->where('puesto_id', $recepId)->where('user_id', $userId)->delete();
                } else {
                    DB::table('empleado_puesto')
                        ->where('puesto_id', $recepId)->where('user_id', $userId)
                        ->update(['puesto_id' => $cajeroId]);
                }
            }

            DB::table('puestos')->where('id', $recepId)->delete();
        } elseif ($recepId && ! $cajeroId) {
            // No existía cajero: reconvertimos el de recepción en cajero.
            DB::table('puestos')->where('id', $recepId)->update(['slug' => 'cajero']);
            $cajeroId = $recepId;
        }

        if ($cajeroId) {
            DB::table('puestos')->where('id', $cajeroId)->update([
                'nombre' => 'Recepcionista / Cajero',
                'descripcion' => 'Atención en recepción y venta de membresías, productos y servicios.',
            ]);
        }
    }

    /**
     * Vuelve a separar un puesto "recepcionista" y restaura el nombre del cajero.
     */
    public function down(): void
    {
        $cajeroId = DB::table('puestos')->where('slug', 'cajero')->value('id');

        if ($cajeroId) {
            DB::table('puestos')->where('id', $cajeroId)->update([
                'nombre' => 'Cajero',
                'descripcion' => 'Vende membresías, productos y servicios.',
            ]);
        }

        if (! DB::table('puestos')->where('slug', 'recepcionista')->exists()) {
            DB::table('puestos')->insert([
                'nombre' => 'Recepcionista',
                'slug' => 'recepcionista',
                'descripcion' => 'Atención en recepción.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
