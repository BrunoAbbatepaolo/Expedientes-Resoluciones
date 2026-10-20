<?php

namespace App\Console\Commands;

use App\Services\Resoluciones\SinExpediente;
use App\Support\Csv;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/** Lista las resoluciones cuyo expediente no existe en el sistema de expedientes del IPV (no modifica nada). */
class ResolucionesSinExpediente extends Command
{
    protected $signature = 'resoluciones:sin-expediente {--csv= : Guarda el listado en este archivo CSV}';

    protected $description = 'Lista las resoluciones cuyo número de expediente no existe en mitivdb';

    public function handle(SinExpediente $informe): int
    {
        $huerfanas = $informe->buscar();

        if ($huerfanas->isEmpty()) {
            $this->info('Todas las resoluciones tienen un expediente válido.');

            return self::SUCCESS;
        }

        $filas = $huerfanas->map(fn ($r) => [
            $r->id,
            $r->numero_resolucion,
            $r->numero_exp,
            $r->fecha ? Carbon::parse($r->fecha)->format('d/m/Y') : '',
            $r->oficina->nombre ?? '',
        ]);

        $this->table(['ID', 'Resolución', 'Expediente', 'Fecha', 'Oficina'], $filas->all());
        $this->warn($huerfanas->count().' resolución(es) con expediente inexistente.');

        if ($ruta = $this->option('csv')) {
            $archivo = fopen($ruta, 'w');
            fwrite($archivo, "\xEF\xBB\xBF");
            fputcsv($archivo, ['ID', 'Resolución', 'Expediente', 'Fecha', 'Oficina'], ';');
            foreach ($filas as $fila) {
                fputcsv($archivo, Csv::fila($fila), ';');
            }
            fclose($archivo);
            $this->info("Listado guardado en $ruta");
        }

        return self::SUCCESS;
    }
}
