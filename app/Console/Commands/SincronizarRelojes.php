<?php

namespace App\Console\Commands;

use App\Services\Rrhh\Relojes\SincronizadorRelojes;
use Illuminate\Console\Command;

/** Lee los relojes activos y guarda sus fichadas nuevas (agente de relojes). */
class SincronizarRelojes extends Command
{
    protected $signature = 'relojes:sincronizar
        {--ip= : Solo este reloj}
        {--mitiv-desde= : Con la doble escritura, revisar el sistema viejo desde esta fecha (Y-m-d) para recuperar fichadas}
        {--forzar : Correr aunque SIREX_RELOJES_ACTIVO esté apagado}';

    protected $description = 'Sincroniza las fichadas de los relojes con SiRex';

    public function handle(SincronizadorRelojes $sincronizador): int
    {
        if (! config('sirex.relojes_activo') && ! $this->option('forzar')) {
            $this->warn('El agente de relojes está apagado (SIREX_RELOJES_ACTIVO=false). Usá --forzar para probarlo.');

            return self::SUCCESS;
        }

        $desde = $this->option('mitiv-desde');
        if ($desde !== null && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $desde)) {
            $this->error('--mitiv-desde debe tener el formato AAAA-MM-DD.');

            return self::FAILURE;
        }

        $resultados = $sincronizador->sincronizar($this->option('ip') ?: null, $desde ? $desde.' 00:00:00' : null);
        if ($resultados === []) {
            $this->warn('No hay relojes activos. Agregá uno con: php artisan relojes:agregar {ip}');

            return self::SUCCESS;
        }

        $this->table(['Reloj', 'Estado', 'Leídas', 'Nuevas', 'Sistema viejo', 'Detalle'], array_map(fn ($r) => [
            $r['reloj']->nombre.' ('.$r['reloj']->ip.')', $r['estado'], $r['leidas'], $r['nuevas'], $r['mitiv'],
            $r['mensaje'] ?? ($r['sin_tarjeta'] ? "{$r['sin_tarjeta']} sin número de tarjeta" : ''),
        ], $resultados));

        return collect($resultados)->every(fn ($r) => $r['estado'] === 'error') ? self::FAILURE : self::SUCCESS;
    }
}
