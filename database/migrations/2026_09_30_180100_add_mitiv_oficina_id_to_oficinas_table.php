<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vincula cada oficina de SiRex con su oficina en el sistema viejo
 * (mitivdb.expediente_oficinas.id). Sin FK: son bases distintas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_admin')->table('oficinas', function (Blueprint $table) {
            $table->unsignedInteger('mitiv_oficina_id')->nullable()->after('codigo');
            $table->index('mitiv_oficina_id');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_admin')->table('oficinas', function (Blueprint $table) {
            $table->dropIndex(['mitiv_oficina_id']);
            $table->dropColumn('mitiv_oficina_id');
        });
    }
};
