<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * numero_exp era único porque en el modo "personalizado" se inventaba un número de trámite al azar.
     * Ahora toda resolución se vincula a un expediente real, y un expediente puede tener más de una
     * resolución (p. ej. una rectificación posterior): pasa a ser un índice común.
     */
    public function up(): void
    {
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->dropUnique(['numero_exp']);
            $table->index('numero_exp');
        });
    }

    public function down(): void
    {
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->dropIndex(['numero_exp']);
            $table->unique('numero_exp');
        });
    }
};
