<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Firmantes que se eligen en la "Plantilla completa" de cada resolución (Administración → Firmantes).
     * Más adelante: firma digital guardada por firmante y código de acceso para aplicarla.
     */
    public function up(): void
    {
        Schema::create('firmantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('cargo', 120);
            $table->string('dependencia', 120)->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        // Los que estaban escritos en las plantillas.
        $ahora = now();
        DB::table('firmantes')->insert(array_map(fn (array $f, int $i) => $f + ['activo' => true, 'orden' => $i, 'created_at' => $ahora, 'updated_at' => $ahora], [
            ['nombre' => 'Arq. HUGO CABRAL', 'cargo' => 'INTERVENTOR', 'dependencia' => 'I.P.V.y D.U.'],
            ['nombre' => 'MARIA GABRIELA LAZARTE', 'cargo' => 'CONTADORA FISCAL', 'dependencia' => 'TRIBUNAL DE CUENTAS'],
            ['nombre' => 'MARTA DEL YAMESEN', 'cargo' => 'DPTO. REC. FINANCIEROS', 'dependencia' => 'I.P.V. y D.U.'],
            ['nombre' => 'C.P.N. FLAVIA PATRICIA CORVALAN', 'cargo' => 'JEFA (Int.) DEPTO. RECURSOS FINANC.', 'dependencia' => 'I.P.V. y D.U.'],
        ], range(1, 4)));
    }

    public function down(): void
    {
        Schema::dropIfExists('firmantes');
    }
};
