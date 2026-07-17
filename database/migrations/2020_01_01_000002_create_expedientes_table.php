<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración de creación "de base" para una tabla legacy, ver
     * 2020_01_01_000000_create_areas_table.php. Columnas inferidas de
     * App\Models\Expediente, database/factories/ExpedienteFactory.php y el uso real
     * en App\Livewire\Expedientes/ExpedienteForm/Detalles.
     *
     * - No incluye 'oficina_id': esa columna la agrega
     *   2025_09_02_120000_add_oficina_id_to_expedientes.php (chequea hasColumn antes
     *   de crearla, así que corre bien después de esta migración).
     * - 'ofi_salida' es un id de oficina (no texto): se usa como
     *   Oficina::find($expediente->ofi_salida) en Expedientes::editar()/selectOficina().
     * - 'deleted_at' es obligatorio: Expediente usa SoftDeletes y ninguna otra
     *   migración lo agrega — sin esto, borrar un expediente rompe con "columna
     *   deleted_at no existe".
     * - Sin ->constrained() en 'ofi_salida'/'cod_area'/'cod_oficina': son referencias
     *   a 'oficinas'/'areas', pero legacy y sin garantía de integridad (igual que
     *   'pases.oficina_id', ver esa migración).
     */
    public function up(): void
    {
        Schema::connection('mysql_admin')->create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->string('num_exp')->nullable();
            $table->string('folio')->nullable();
            $table->string('causante')->nullable();
            $table->string('asunto')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->unsignedBigInteger('ofi_salida')->nullable();
            $table->string('cod_area')->nullable();
            $table->string('cod_oficina')->nullable();
            $table->date('fecha_salida')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('num_exp');
            $table->index('cod_area');
            $table->index('cod_oficina');
            $table->index('ofi_salida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_admin')->dropIfExists('expedientes');
    }
};
