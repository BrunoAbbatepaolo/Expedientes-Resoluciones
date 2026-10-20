<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * numero_resolucion pasa a ser opcional: en la práctica se asigna al firmar y el borrador sale sin número
     * (se completa después desde la lista). documento_html guarda el documento ya armado al guardar, para que
     * un cambio posterior en la plantilla no modifique las resoluciones viejas.
     */
    public function up(): void
    {
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->string('numero_resolucion')->nullable()->change();
            $table->longText('documento_html')->nullable()->after('datos');
        });
    }

    public function down(): void
    {
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->dropColumn('documento_html');
        });
    }
};
