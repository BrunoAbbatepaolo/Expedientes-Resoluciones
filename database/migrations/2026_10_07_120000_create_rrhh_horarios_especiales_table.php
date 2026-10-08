<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Horarios especiales cargados en SiRex (ej. 8 a 13). Mientras estén vigentes reemplazan
 * al horario del sistema viejo para calcular la asistencia. empleado_id = mitivdb.empleados.id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_admin')->create('rrhh_horarios_especiales', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empleado_id')->index();
            $table->json('dias');
            $table->time('desde_maniana');
            $table->time('hasta_maniana');
            $table->time('desde_tarde')->nullable();
            $table->time('hasta_tarde')->nullable();
            $table->unsignedSmallInteger('tolerancia')->nullable();
            $table->date('vigente_desde');
            $table->date('vigente_hasta')->nullable();
            $table->string('motivo');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_admin')->dropIfExists('rrhh_horarios_especiales');
    }
};
