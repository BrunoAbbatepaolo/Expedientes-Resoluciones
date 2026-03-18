<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::connection('mysql_admin')->table('pases', function (Blueprint $table) {
        $table->unsignedBigInteger('oficina_destino_id')->nullable()->after('oficina_id');
        $table->boolean('firmado')->default(false)->after('observacion');
    });
}

public function down()
{
    Schema::connection('mysql_admin')->table('pases', function (Blueprint $table) {
        $table->dropColumn(['oficina_destino_id', 'firmado']);
    });
}
};
