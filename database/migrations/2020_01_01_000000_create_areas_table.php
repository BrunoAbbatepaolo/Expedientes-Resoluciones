<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración de creación "de base" para una tabla legacy (sistema anterior a esta
     * app) que nunca tuvo su create_table en el repo, solo alteraciones posteriores.
     * Fechada en 2020 a propósito para correr antes que cualquier otra migración
     * existente y que `migrate:fresh` funcione en un entorno 100% vacío.
     * Columnas inferidas de App\Models\Area, database/factories/AreaFactory.php y
     * el uso real en App\Models\Oficina::area()/Expediente::area() (join por 'codigo').
     */
    public function up(): void
    {
        Schema::connection('mysql_legui')->create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('codigo')->nullable();
            $table->index('codigo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_legui')->dropIfExists('areas');
    }
};
