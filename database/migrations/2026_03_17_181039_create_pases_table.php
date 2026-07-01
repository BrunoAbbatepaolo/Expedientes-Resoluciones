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
        Schema::connection('mysql_admin')->create('pases', function (Blueprint $table) {
    $table->id();
    $table->foreignId('expediente_id')->constrained('expedientes');
    $table->unsignedBigInteger('oficina_id');        // oficina que LO TIENE
    $table->unsignedBigInteger('oficina_destino_id')->nullable(); // a donde va
    $table->date('fecha_ingreso');                   // cuando entró a esta oficina
    $table->date('fecha_salida')->nullable();         // cuando salió
    $table->string('observaciones')->nullable();
    $table->unsignedBigInteger('user_id')->nullable(); // quién registró el pase
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pases');
    }
};
