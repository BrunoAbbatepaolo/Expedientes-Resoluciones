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
        // CrearResolucion::guardarPlantilla()/guardar()/guardarPersonalizado() ya
        // intentaban guardar el HTML de la resolución en una columna 'plantilla'
        // que nunca existió (se descartaba en silencio por mass-assignment). Además
        // 'pdf' y 'fecha_ingreso' eran NOT NULL sin default pese a que ningún flujo
        // de creación las completa (los PDFs van por la tabla resolucion_archivos).
        // Ver implementacion_futuro.md, punto 1.2.
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->longText('plantilla')->nullable()->after('numero_resolucion');
        });

        Schema::table('resoluciones', function (Blueprint $table) {
            $table->string('pdf')->nullable()->change();
            $table->dateTime('fecha_ingreso')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->dropColumn('plantilla');
        });
    }
};
