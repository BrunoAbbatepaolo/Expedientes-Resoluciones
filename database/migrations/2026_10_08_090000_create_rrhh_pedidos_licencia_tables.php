<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pedido de licencias desde Mi RRHH (spec sección 7 bis). empleado_id, oficina_id y
 * tipo_ausencia_id son ids de mitivdb (sin FK: bases distintas).
 */
return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('mysql_admin');

        $schema->create('rrhh_tipos_pedibles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tipo_ausencia_id')->unique();
            $table->string('nombre');
            $table->boolean('permite_retroactivo')->default(false);
            $table->boolean('requiere_adjunto')->default(false);
            $table->timestamps();
        });

        $schema->create('rrhh_pedidos_licencia', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empleado_id')->index();
            $table->unsignedInteger('oficina_id')->index();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('tipo_ausencia_id');
            $table->string('tipo_nombre');
            $table->boolean('es_vacaciones')->default(false);
            $table->date('desde');
            $table->date('hasta');
            $table->unsignedSmallInteger('dias_habiles');
            $table->string('observacion', 500)->nullable();
            $table->string('adjunto_ruta')->nullable();
            $table->string('adjunto_nombre')->nullable();
            $table->string('estado', 20)->index();
            $table->unsignedBigInteger('jefe_id')->nullable();
            $table->timestamp('jefe_at')->nullable();
            $table->string('jefe_motivo', 500)->nullable();
            $table->unsignedBigInteger('rrhh_id')->nullable();
            $table->timestamp('rrhh_at')->nullable();
            $table->string('rrhh_motivo', 500)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql_admin');
        $schema->dropIfExists('rrhh_pedidos_licencia');
        $schema->dropIfExists('rrhh_tipos_pedibles');
    }
};
