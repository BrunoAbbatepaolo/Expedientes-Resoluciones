<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('mysql_admin')->table('expedientes', function (Blueprint $table) {
            if (!Schema::connection('mysql_admin')->hasColumn('expedientes', 'oficina_id')) {
                $table->unsignedBigInteger('oficina_id')->nullable()->after('id');
                $table->index('oficina_id', 'expedientes_oficina_id_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_admin')->table('expedientes', function (Blueprint $table) {
            if (Schema::connection('mysql_admin')->hasColumn('expedientes', 'oficina_id')) {
                $table->dropIndex('expedientes_oficina_id_idx');
                $table->dropColumn('oficina_id');
            }
        });
    }
};
