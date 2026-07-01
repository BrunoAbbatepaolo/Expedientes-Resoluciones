<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ImportarPases extends Command
{
    protected $signature = 'importar:pases {--fresh : Limpia pases importados antes de reimportar}';
    protected $description = 'Importa historial de pases desde Postgres a MySQL';

    // Mapeo expediente_oficinas (EO) → oficinas MySQL (MY)
    private $mapeoEO = [
        8   => 59,  9   => 20,  10  => 1,   11  => 21,
        12  => 27,  13  => 25,  14  => 19,  15  => 18,
        16  => 74,  17  => 20,  18  => 6,   19  => 10,
        20  => 2,   22  => 37,  23  => 68,  25  => 12,
        31  => 29,  32  => 15,  34  => 32,  35  => 73,
        37  => 80,  40  => 73,  43  => 73,  44  => 61,
        45  => 61,  46  => 16,  48  => 31,  51  => 5,
        52  => 17,  57  => 76,  58  => 40,  59  => 60,
        60  => 22,  62  => 26,  72  => 36,  79  => 41,
        97  => 5,   98  => 73,  107 => 59,  111 => 4,
        121 => 52,  126 => 6,   133 => 32,  137 => 22,
        141 => 60,  153 => 78,  239 => 29,  242 => 30,
        247 => null,285 => 73,  286 => 73,  347 => 29,
        364 => 36,  368 => null,374 => 73,  377 => 73,
        380 => 76,  382 => 6,   394 => 78,  543 => 7,
        565 => null,568 => null,577 => 5,   579 => 85,
        581 => 84,  582 => 28,  583 => 24,  585 => 44,
        592 => 34,  596 => 30,  602 => 41,  618 => 63,
        1   => 34,  2   => 61,  3   => 72,  4   => 5,
        5   => 52,  7   => 44,
        0   => null, // NO DEFINIDO
	98  => 89,  // DIRECCION AREA REC.Y REG.DOMINIAL → DIRECC. ÁREA REC. Y REG. DOMINIAL
285 => 89,  // DIRECCIÓN AREA REC.Y REG.DOMINIAL → DIRECC. ÁREA REC. Y REG. DOMINIAL
    ];

    private int $maxErrores = 50;

    public function handle()
    {
        // ── PASO 1: Oficinas nuevas ──────────────────────────────────────────
        $this->info('Paso 1: Verificando oficinas...');
        $oficinasNuevas = [
            ['id'=>82, 'cod_area'=>0, 'codigo'=>0, 'nombre'=>'ADSCRIPTOS AL IPV',               'created_at'=>now(), 'updated_at'=>now()],
            ['id'=>83, 'cod_area'=>0, 'codigo'=>0, 'nombre'=>'COMISION DE SERVICIOS',            'created_at'=>now(), 'updated_at'=>now()],
            ['id'=>84, 'cod_area'=>0, 'codigo'=>0, 'nombre'=>'COORD. SOCIO COMUNITARIA',         'created_at'=>now(), 'updated_at'=>now()],
            ['id'=>85, 'cod_area'=>0, 'codigo'=>0, 'nombre'=>'DPTO. COMUNICACION INSTITUCIONAL', 'created_at'=>now(), 'updated_at'=>now()],
            ['id'=>86, 'cod_area'=>0, 'codigo'=>0, 'nombre'=>'PROCREAR',                         'created_at'=>now(), 'updated_at'=>now()],
            ['id'=>87, 'cod_area'=>0, 'codigo'=>0, 'nombre'=>'SECCION REGISTRO GENERAL',         'created_at'=>now(), 'updated_at'=>now()],
        ];
        foreach ($oficinasNuevas as $o) {
            $existe = DB::connection('mysql_admin')->table('oficinas')->where('id', $o['id'])->exists();
            if (!$existe) {
                DB::connection('mysql_admin')->table('oficinas')->insert($o);
                $this->line("  ✓ Insertada: {$o['nombre']}");
            } else {
                $this->line("  - Ya existe: {$o['nombre']}");
            }
        }

        // ── PASO 2: Verificar tabla pases ────────────────────────────────────
        $this->info('Paso 2: Verificando tabla pases...');
        if (!Schema::connection('mysql_admin')->hasTable('pases')) {
            Schema::connection('mysql_admin')->create('pases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('expediente_id');
                $table->unsignedBigInteger('oficina_id');
                $table->unsignedBigInteger('oficina_origen_id')->nullable();
                $table->date('fecha');
                $table->time('hora')->nullable();
                $table->text('observacion')->nullable();
                $table->integer('folio')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->boolean('importado')->default(false);
                $table->boolean('firmado')->default(false);
                $table->timestamps();
                $table->index('expediente_id');
                $table->index('oficina_id');
                $table->index('oficina_origen_id');
                $table->index('fecha');
            });
            $this->info('  ✓ Tabla pases creada');
        } else {
            $this->info('  - Tabla pases ya existe');
        }

        // ── PASO 3: Idempotencia ─────────────────────────────────────────────
        $yaImportados = DB::connection('mysql_admin')
            ->table('pases')->where('importado', true)->exists();

        if ($yaImportados && !$this->option('fresh')) {
            $this->error('Ya existen pases importados. Usá --fresh para reimportar.');
            return 1;
        }

        if ($this->option('fresh')) {
            $borrados = DB::connection('mysql_admin')
                ->table('pases')->where('importado', true)->delete();
            $this->warn("  ⚠ --fresh: eliminados {$borrados} pases importados");
        }

        // ── PASO 4: Validar unicidad num_exp ─────────────────────────────────
        $this->info('Paso 4: Validando unicidad de num_exp...');
        $duplicados = DB::connection('mysql_admin')
            ->table('expedientes')
            ->select('num_exp', DB::raw('COUNT(*) as c'))
            ->whereNotNull('num_exp')
            ->groupBy('num_exp')
            ->having('c', '>', 1)
            ->count();

        if ($duplicados > 0) {
            $this->error("Hay {$duplicados} num_exp duplicados.");
            return 1;
        }
        $this->info('  ✓ num_exp únicos');

        // ── PASO 5: Cargar datos ─────────────────────────────────────────────
        $this->info('Paso 5: Cargando expedientes...');

        $expedientesMysql = DB::connection('mysql_admin')
            ->table('expedientes')
            ->select('id', 'num_exp')
            ->whereNotNull('num_exp')
            ->get()
            ->keyBy('num_exp');

        $expedientesPg = DB::connection('pgsql_mitiv')
            ->table('expedientes')
            ->select('id', 'numero')
            ->get()
            ->keyBy('id');

        $numerosMySQL = $expedientesMysql->keys()->toArray();
        $idsPg = DB::connection('pgsql_mitiv')
            ->table('expedientes')
            ->whereIn('numero', $numerosMySQL)
            ->pluck('id')
            ->toArray();

        $this->info("  MySQL: {$expedientesMysql->count()} | Expedientes en Postgres: " . count($idsPg));

        if (empty($idsPg)) {
            $this->warn('  No se encontraron expedientes en Postgres.');
            return 0;
        }

        // ── PASO 6: Importar desde expediente_pases_origen ───────────────────
        $this->info('Paso 6: Importando pases desde expediente_pases_origen...');
        $total         = 0;
        $omitidos      = 0;
        $sinExpediente = 0;
        $errorCount    = 0;

        DB::connection('pgsql_mitiv')
            ->table('expediente_pases_origen')
            ->whereIn('expediente_id', $idsPg)
            ->orderBy('id')
            ->chunk(500, function ($pases) use (
                &$total, &$omitidos, &$sinExpediente, &$errorCount,
                $expedientesMysql, $expedientesPg
            ) {
                if ($errorCount >= $this->maxErrores) return false;

                $insertar = [];

                foreach ($pases as $pase) {
                    // Mapear destino
                    $destinoMyId = isset($this->mapeoEO[$pase->oficina_id])
                        ? $this->mapeoEO[$pase->oficina_id]
                        : null;

                    // Ignorar si no tiene mapeo o es NO DEFINIDO
                    if (!$destinoMyId) { $omitidos++; continue; }

                    // Ignorar el primer pase (origen=0/NO DEFINIDO → destino=MESA)
                    // Es el ingreso externo al sistema
                    if (is_null($pase->origen_id) || $pase->origen_id === 0) {
                        $omitidos++;
                        continue;
                    }

                    // Mapear origen
                    $origenMyId = isset($this->mapeoEO[$pase->origen_id])
                        ? $this->mapeoEO[$pase->origen_id]
                        : null;

                    // Buscar expediente
                    $expPg = $expedientesPg->get($pase->expediente_id);
                    if (!$expPg) { $sinExpediente++; continue; }

                    $expMy = $expedientesMysql->get($expPg->numero);
                    if (!$expMy) { $sinExpediente++; continue; }

                    // Normalizar hora
                    $hora = null;
                    if (!empty($pase->hora)) {
                        $hora = substr((string) $pase->hora, 0, 8);
                    }

                    // Normalizar observacion
                    $obsRaw = $pase->observacion;
                    $obs = ($obsRaw === null
                        || $obsRaw === '\N'
                        || (is_string($obsRaw) && trim($obsRaw) === ''))
                        ? null
                        : $obsRaw;

                    $insertar[] = [
                        'expediente_id'     => $expMy->id,
                        'oficina_id'        => $destinoMyId,
                        'oficina_origen_id' => $origenMyId,
                        'fecha'             => $pase->fecha,
                        'hora'              => $hora,
                        'observacion'       => $obs,
                        'folio'             => $pase->folio,
                        'user_id'           => null,
                        'importado'         => true,
                        'firmado'           => false,
                        'created_at'        => ($pase->created_at && $pase->created_at > '1970-01-01')
                                                ? $pase->created_at : now(),
                        'updated_at'        => ($pase->updated_at && $pase->updated_at > '1970-01-01')
                                                ? $pase->updated_at : now(),
                    ];
                }

                if (empty($insertar)) return;

                try {
                    DB::connection('mysql_admin')->transaction(function () use ($insertar) {
                        DB::connection('mysql_admin')->table('pases')->insert($insertar);
                    });
                    $total += count($insertar);
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("ImportarPases lote falló: " . $e->getMessage());
                    $this->warn("  ✗ Lote falló ({$errorCount}/{$this->maxErrores}): " . substr($e->getMessage(), 0, 80));
                    if ($errorCount >= $this->maxErrores) {
                        $this->error('Demasiados errores. Revisá laravel.log');
                        return false;
                    }
                }

                $this->line("  ✓ {$total} importados | {$omitidos} sin mapeo | {$sinExpediente} sin expediente");
            });

        // ── RESUMEN ──────────────────────────────────────────────────────────
        $this->info("\n=== RESUMEN FINAL ===");
        $this->info("✓ Pases importados: {$total}");
        $this->warn("- Sin mapeo: {$omitidos}");
        $this->warn("- Sin expediente: {$sinExpediente}");

        if ($errorCount > 0) {
            $this->error("✗ Errores: {$errorCount} — revisá laravel.log");
            return 1;
        }

        $this->info('✓ Importación completada sin errores');
        $this->info('Para reimportar: php artisan importar:pases --fresh');
        return 0;
    }
}
