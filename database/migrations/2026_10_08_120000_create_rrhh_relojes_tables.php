<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agente de relojes (docs/spec-agente-relojes.md): relojes que consulta SiRex y las fichadas que
 * lee de ellos. Las horas de las fichadas son las del reloj (hora local, sin zona).
 */
return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('mysql_admin');

        $schema->create('rrhh_relojes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ip', 45)->unique();
            $table->unsignedSmallInteger('puerto')->default(4370);
            $table->string('tipo', 20)->default('huella');
            $table->boolean('activo')->default(true);
            $table->string('serie')->nullable();
            $table->string('modelo')->nullable();
            $table->string('firmware')->nullable();
            $table->timestamp('ultimo_contacto_at')->nullable();
            $table->dateTime('ultima_fichada_at')->nullable();
            $table->unsignedInteger('registros_en_reloj')->nullable();
            $table->integer('desvio_segundos')->nullable();
            $table->unsignedSmallInteger('fallas_seguidas')->default(0);
            $table->string('ultimo_error', 500)->nullable();
            $table->timestamp('ultimo_error_at')->nullable();
            $table->timestamps();
        });

        $schema->create('rrhh_fichadas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reloj_id');
            $table->unsignedInteger('card_id');
            $table->dateTime('fecha_hora');
            $table->unsignedSmallInteger('status')->nullable();
            $table->unsignedSmallInteger('punch')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['reloj_id', 'card_id', 'fecha_hora']);
            $table->index(['card_id', 'fecha_hora']);
            $table->index('fecha_hora');
            $table->foreign('reloj_id')->references('id')->on('rrhh_relojes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql_admin');
        $schema->dropIfExists('rrhh_fichadas');
        $schema->dropIfExists('rrhh_relojes');
    }
};
