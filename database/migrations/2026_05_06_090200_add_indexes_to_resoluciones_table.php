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
        // numero_exp/numero_resolucion se usan para relacionar (VistaExpedientes)
        // y para evitar duplicados (ver generarNumeroTramite() en CrearResolucion)
        // pero no tenían índice ni constraint. Ver implementacion_futuro.md 2.7.
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->unique('numero_exp');
            $table->index('numero_resolucion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->dropUnique(['numero_exp']);
            $table->dropIndex(['numero_resolucion']);
        });
    }
};
