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
        // Esquema alineado con el que ya usa ImportarPases::handle() en producción
        // (la migración original tenía columnas distintas —fecha_ingreso/fecha_salida/
        // observaciones— y las 2 migraciones siguientes agregaban columnas duplicadas
        // o con "after" apuntando a una columna que no existía; ver implementacion_futuro.md 2.6).
        Schema::connection('mysql_admin')->create('pases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes');
            // oficina_id/oficina_origen_id/oficina_destino_id sin ->constrained(): el
            // mapeo de ImportarPases contra datos legacy puede traer ids sin
            // correspondencia exacta en 'oficinas', igual que el resto de la app
            // (ver Permiso::oficina_id). No forzamos FK para no romper el import.
            $table->unsignedBigInteger('oficina_id'); // oficina que recibe / tiene el expediente tras el pase
            $table->unsignedBigInteger('oficina_origen_id')->nullable();
            $table->unsignedBigInteger('oficina_destino_id')->nullable(); // reservado para pases pendientes de confirmar
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->text('observacion')->nullable();
            $table->integer('folio')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users'); // quién registró el pase (null si fue importado)
            $table->boolean('importado')->default(false);
            $table->boolean('firmado')->default(false);
            $table->timestamps();

            $table->index('oficina_id');
            $table->index('oficina_origen_id');
            $table->index('oficina_destino_id');
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_admin')->dropIfExists('pases');
    }
};
