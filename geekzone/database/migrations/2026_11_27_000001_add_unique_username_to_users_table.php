<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El registro permitía dos usuarios con el mismo username, y luego el perfil
     * de uno de ellos no se podía editar (ProfileController exige username único).
     */
    public function up(): void
    {
        $duplicados = DB::table('users')
            ->select('username')
            ->groupBy('username')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('username');

        if ($duplicados->isNotEmpty()) {
            throw new RuntimeException(
                'No se puede añadir el índice único a users.username: hay nombres duplicados ('
                .$duplicados->implode(', ').'). Renómbralos y vuelve a migrar.'
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
        });
    }
};
