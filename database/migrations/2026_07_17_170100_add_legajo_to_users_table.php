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
        // Necesaria para el alta de usuarios por administrador (ver
        // ListaUsuario::crearUsuario()). Nullable porque los usuarios ya
        // cargados no tienen legajo asignado retroactivamente.
        Schema::connection('mysql_admin')->table('users', function (Blueprint $table) {
            $table->string('legajo')->nullable()->unique()->after('apellido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_admin')->table('users', function (Blueprint $table) {
            $table->dropColumn('legajo');
        });
    }
};
