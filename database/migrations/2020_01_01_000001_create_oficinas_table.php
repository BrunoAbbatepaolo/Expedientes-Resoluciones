<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración de creación "de base" para una tabla legacy, ver
     * 2020_01_01_000000_create_areas_table.php. Columnas inferidas de
     * App\Models\Oficina, database/factories/OficinaFactory.php y el uso real en
     * App\Console\Commands\ImportarPases::handle() (Paso 1: insert directo con
     * id/cod_area/codigo/nombre).
     *
     * Sin FK a 'areas': 'oficinas' vive en mysql_admin y 'areas' en mysql_legui
     * (conexiones/bases distintas), igual que el resto de las referencias cruzadas
     * de este proyecto (ver 'pases', 'permisos', 'resoluciones').
     */
    public function up(): void
    {
        Schema::connection('mysql_admin')->create('oficinas', function (Blueprint $table) {
            $table->id();
            $table->string('cod_area')->nullable();
            $table->string('nombre')->nullable();
            $table->string('codigo')->nullable();
            $table->index('cod_area');
            $table->index('codigo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_admin')->dropIfExists('oficinas');
    }
};
