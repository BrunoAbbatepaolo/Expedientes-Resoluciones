<?php

namespace App\Console\Commands;

use App\Models\Mitiv\OficinaRrhh;
use App\Models\RrhhOficinaPadre;
use App\Services\Rrhh\Organigrama;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Precarga la dependencia entre oficinas RRHH desde database/data/organigrama_rrhh.php.
 * No pisa lo que ya esté cargado y saltea las oficinas cuyo nombre no coincide.
 */
class CargarOrganigramaRrhh extends Command
{
    protected $signature = 'rrhh:cargar-organigrama {--simular : Muestra qué haría sin guardar}';

    protected $description = 'Carga la dependencia entre oficinas de RRHH según el organigrama funcional';

    public function handle(Organigrama $organigrama): int
    {
        $mapa = require database_path('data/organigrama_rrhh.php');
        $nombres = OficinaRrhh::pluck('nombre', 'id');
        $cargadas = RrhhOficinaPadre::whereNotNull('padre_mitiv_oficina_id')->pluck('mitiv_oficina_id')->flip();
        $aplicadas = 0;
        $salteadas = [];

        foreach ($mapa as $id => [$esperado, $padre]) {
            $motivo = match (true) {
                ! isset($nombres[$id]) => 'no existe en el sistema de personal',
                self::normalizar($nombres[$id]) !== self::normalizar($esperado) => 'el nombre actual es "'.trim($nombres[$id]).'"',
                ! isset($nombres[$padre]) => "la oficina superior ($padre) no existe",
                isset($cargadas[$id]) => 'ya tenía superior cargado',
                default => null,
            };

            if ($motivo === null && ! $this->option('simular')) {
                try {
                    $organigrama->asignarPadre($id, $padre);
                } catch (InvalidArgumentException $e) {
                    $motivo = $e->getMessage();
                }
            }

            if ($motivo !== null) {
                $salteadas[] = [$id, $esperado, $motivo];
            } else {
                $aplicadas++;
            }
        }

        $this->info(($this->option('simular') ? 'Se aplicarían' : 'Aplicadas').": $aplicadas");
        if ($salteadas !== []) {
            $this->warn('Salteadas: '.count($salteadas));
            $this->table(['Id', 'Oficina', 'Motivo'], $salteadas);
        }

        return self::SUCCESS;
    }

    private static function normalizar(string $nombre): string
    {
        return preg_replace('/[^A-Z0-9]/', '', Str::upper(Str::ascii($nombre)));
    }
}
