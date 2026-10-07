<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos propios de SiRex para el módulo RRHH (fase 1). Las oficinas son del catálogo
 * de RRHH del sistema viejo (mitivdb.oficinas.id): sin FK, son bases distintas.
 */
return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('mysql_admin');

        // Dependencia entre oficinas (organigrama). Sin fila o padre null = sin superior.
        $schema->create('rrhh_oficina_padres', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mitiv_oficina_id')->unique();
            $table->unsignedInteger('padre_mitiv_oficina_id')->nullable()->index();
            $table->timestamps();
        });

        // Jefes: ven su oficina y todas las que dependen de ella.
        $schema->create('rrhh_jefaturas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('mitiv_oficina_id');
            $table->timestamps();

            $table->unique(['user_id', 'mitiv_oficina_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // Quién miró datos personales o sensibles de otra persona.
        $schema->create('rrhh_accesos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('empleado_id');
            $table->string('nivel', 20);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['empleado_id', 'created_at']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql_admin');
        $schema->dropIfExists('rrhh_accesos');
        $schema->dropIfExists('rrhh_jefaturas');
        $schema->dropIfExists('rrhh_oficina_padres');
    }
};
