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
        Schema::connection('mysql_admin')->table('pases', function (Blueprint $table) {
            // 'pendiente' hasta que la oficina destino acepte el pase (bandeja de entrada);
            // 'aceptado' una vez confirmado (los pases importados ya se consideran resueltos).
            $table->string('estado')->default('pendiente')->after('firmado');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_admin')->table('pases', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropColumn('estado');
        });
    }
};
