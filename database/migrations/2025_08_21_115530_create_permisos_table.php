<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // permisos vive en mysql_admin, junto a la tabla users que referencia
        // (antes se creaba en la conexión default, ver implementacion_futuro.md 2.7)
        Schema::connection('mysql_admin')->create('permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('nombre'); // ejemplo: expediente_ver, expediente_editar, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_admin')->dropIfExists('permisos');
    }
};
