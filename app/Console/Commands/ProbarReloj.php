<?php

namespace App\Console\Commands;

use App\Services\Rrhh\Relojes\ErrorReloj;
use App\Services\Rrhh\Relojes\FabricaClienteReloj;
use Illuminate\Console\Command;

/**
 * Diagnóstico de SOLO LECTURA de un reloj: se conecta, informa el equipo, la memoria y un resumen
 * de las fichadas. No guarda nada ni modifica el reloj.
 */
class ProbarReloj extends Command
{
    protected $signature = 'relojes:probar {ip : IP del reloj} {--puerto=4370}';

    protected $description = 'Prueba la conexión con un reloj y muestra un resumen (solo lectura)';

    public function handle(FabricaClienteReloj $fabrica): int
    {
        $ip = (string) $this->argument('ip');
        $inicio = microtime(true);

        try {
            $cliente = $fabrica->crear($ip, (int) $this->option('puerto'));
        } catch (ErrorReloj $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        try {
            $cliente->conectar();
            $info = $cliente->informacion();
            $memoria = $cliente->memoria();

            $this->info("Conectado a {$ip} en ".round((microtime(true) - $inicio) * 1000).' ms');
            $this->table(['Dato', 'Valor'], [
                ['Modelo', trim(($info['nombre'] ?? '?').' '.($info['plataforma'] ?? ''))],
                ['Firmware', $info['firmware'] ?? '?'],
                ['Número de serie', $info['serie'] ?? '?'],
                ['Hora del reloj', $info['hora']],
                ['Fichadas guardadas', number_format($memoria['registros'], 0, ',', '.').' de '.number_format($memoria['capacidad_registros'], 0, ',', '.')],
                ['Usuarios cargados', number_format($memoria['usuarios'], 0, ',', '.')],
            ]);

            $lectura = $cliente->leerFichadas();
            $porDia = [];
            foreach ($lectura->fichadas as $f) {
                $dia = substr($f->fechaHora, 0, 10);
                $porDia[$dia] = ($porDia[$dia] ?? 0) + 1;
            }
            ksort($porDia);

            $this->line('Fichadas leídas: '.count($lectura->fichadas)." ({$lectura->bytesPorRegistro} bytes por registro), descartadas: {$lectura->descartadas}");
            if ($porDia !== []) {
                $this->line('Primera: '.min(array_column(array_map(fn ($f) => (array) $f, $lectura->fichadas), 'fechaHora')));
                $this->line('Última: '.max(array_column(array_map(fn ($f) => (array) $f, $lectura->fichadas), 'fechaHora')));
                $this->line('Últimos días: '.collect($porDia)->slice(-4)->map(fn ($n, $d) => "$d: $n")->implode(' · '));
            }
            $this->line('Tiempo total: '.round(microtime(true) - $inicio, 1).' s');

            return self::SUCCESS;
        } catch (ErrorReloj $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } finally {
            $cliente->desconectar();
        }
    }
}
