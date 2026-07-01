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
        Schema::table('resoluciones', function (Blueprint $table) {
            // Usamos dateTime porque en el input pusimos datetime-local (guarda fecha y hora)
            $table->dateTime('fecha_ingreso')->after('fecha');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resoluciones', function (Blueprint $table) {
            //
        });
    }
};
