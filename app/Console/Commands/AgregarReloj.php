<?php

namespace App\Console\Commands;

use App\Models\RrhhReloj;
use App\Services\Rrhh\Relojes\ErrorReloj;
use App\Services\Rrhh\Relojes\FabricaClienteReloj;
use Illuminate\Console\Command;

/** Da de alta (o reactiva) un reloj que SiRex va a consultar. Prueba la conexión en modo solo lectura. */
class AgregarReloj extends Command
{
    protected $signature = 'relojes:agregar {ip : IP del reloj}
        {--nombre= : Nombre para mostrar (por defecto, la IP)}
        {--tipo=huella : huella o facial}
        {--puerto=4370}
        {--sin-probar : No conectarse al reloj para verificarlo}';

    protected $description = 'Agrega un reloj para que el agente lo consulte';

    public function handle(FabricaClienteReloj $fabrica): int
    {
        $ip = (string) $this->argument('ip');
        $tipo = (string) $this->option('tipo');

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $this->error('La IP no es válida.');

            return self::FAILURE;
        }
        if (! in_array($tipo, ['huella', 'facial'], true)) {
            $this->error('El tipo debe ser huella o facial.');

            return self::FAILURE;
        }

        $datos = [];
        if (! $this->option('sin-probar')) {
            try {
                $cliente = $fabrica->crear($ip, (int) $this->option('puerto'));
                try {
                    $cliente->conectar();
                    $info = $cliente->informacion();
                    $datos = [
                        'serie' => $info['serie'], 'firmware' => $info['firmware'],
                        'modelo' => trim(($info['nombre'] ?? '').' '.($info['plataforma'] ?? '')) ?: null,
                    ];
                    $this->info('El reloj respondió: '.($datos['modelo'] ?? 'modelo desconocido').', serie '.($datos['serie'] ?? '?').'.');
                } finally {
                    $cliente->desconectar();
                }
            } catch (ErrorReloj $e) {
                $this->error("No se pudo verificar el reloj: {$e->getMessage()} (usá --sin-probar para agregarlo igual).");

                return self::FAILURE;
            }
        }

        $reloj = RrhhReloj::updateOrCreate(['ip' => $ip], [
            'nombre' => $this->option('nombre') ?: $ip, 'tipo' => $tipo, 'puerto' => (int) $this->option('puerto'), 'activo' => true,
        ] + $datos);

        $this->info("Reloj {$reloj->nombre} ({$reloj->ip}) listo para sincronizar.");

        return self::SUCCESS;
    }
}
