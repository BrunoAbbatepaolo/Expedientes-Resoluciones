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
        // Sin ->constrained(): 'resoluciones' vive en la conexión default (bd 'laravel')
        // y 'oficinas' en 'mysql_admin' (bd 'admin'), igual que oficina_id/oficina_origen_id
        // en la migración de 'pases' — un FK real cruzando conexiones no es fiable aquí.
        // Nullable a propósito: las resoluciones existentes quedan sin oficina (ver
        // implementacion_futuro.md 2.1) hasta que se les asigne una manualmente.
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->unsignedBigInteger('oficina_id')->nullable()->after('id');
            $table->index('oficina_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->dropIndex(['oficina_id']);
            $table->dropColumn('oficina_id');
        });
    }
};
