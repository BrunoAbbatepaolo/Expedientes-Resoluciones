<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expedientes del IPV que un usuario sigue ("favoritos"). Se guarda el número (no el id
 * de mitivdb) y la fecha del último pase que el usuario ya vio, para avisar si se movió.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_admin')->create('seguimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('numero_exp', 50);
            $table->dateTime('ultimo_pase_visto')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'numero_exp']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_admin')->dropIfExists('seguimientos');
    }
};
