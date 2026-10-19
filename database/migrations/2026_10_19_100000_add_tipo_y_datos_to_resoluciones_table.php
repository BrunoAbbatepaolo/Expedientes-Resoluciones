<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las resoluciones hechas con la "Plantilla completa" guardan el tipo y los datos cargados para poder
     * volver a armar el documento con su diseño al imprimir: 'plantilla' pasa por Purifier, que quita los
     * estilos y las clases del prototipo.
     */
    public function up(): void
    {
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->string('tipo', 60)->nullable()->after('numero_resolucion');
            $table->json('datos')->nullable()->after('plantilla');
        });
    }

    public function down(): void
    {
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'datos']);
        });
    }
};
