<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::connection('mysql_admin')->table('pases', function (Blueprint $table) {
        $table->unsignedBigInteger('oficina_origen_id')->nullable()->after('oficina_id');
        $table->index('oficina_origen_id');
    });
}

public function down()
{
    Schema::connection('mysql_admin')->table('pases', function (Blueprint $table) {
        $table->dropIndex(['oficina_origen_id']);
        $table->dropColumn('oficina_origen_id');
    });
}
};
