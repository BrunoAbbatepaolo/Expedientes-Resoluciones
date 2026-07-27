<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $conn = 'mysql_admin';

    private string $table = 'permisos';

    private string $uniqueName = 'permisos_user_nombre_unique';

    public function up(): void
    {
        // 1) Agregar columna oficina_id SOLO si no existe
        if (! Schema::connection($this->conn)->hasColumn($this->table, 'oficina_id')) {
            Schema::connection($this->conn)->table($this->table, function (Blueprint $table) {
                // NO usamos ->constrained() porque 'oficinas' está en otra conexión (mysql_legui)
                $table->unsignedBigInteger('oficina_id')->nullable()->after('nombre');
            });
        }

        // 2) Crear índice único SOLO si no existe
        // hasIndex() en vez de "SHOW INDEX FROM" (MySQL-only) para que la migración
        // también corra en sqlite (tests, ver phpunit.xml).
        if (! Schema::connection($this->conn)->hasIndex($this->table, $this->uniqueName)) {
            Schema::connection($this->conn)->table($this->table, function (Blueprint $table) {
                $table->unique(['user_id', 'nombre'], $this->uniqueName);
            });
        }
    }

    public function down(): void
    {
        // Quitar índice único si existe
        if (Schema::connection($this->conn)->hasIndex($this->table, $this->uniqueName)) {
            Schema::connection($this->conn)->table($this->table, function (Blueprint $table) {
                $table->dropUnique($this->uniqueName);
            });
        }

        // Quitar columna si existe
        if (Schema::connection($this->conn)->hasColumn($this->table, 'oficina_id')) {
            Schema::connection($this->conn)->table($this->table, function (Blueprint $table) {
                $table->dropColumn('oficina_id');
            });
        }
    }
};
