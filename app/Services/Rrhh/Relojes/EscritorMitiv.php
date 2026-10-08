<?php

namespace App\Services\Rrhh\Relojes;

use App\Services\Rrhh\Hora;
use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

/**
 * Doble escritura temporal en el sistema viejo (docs/spec-agente-relojes.md): mientras el sistema
 * viejo siga en uso, necesita que las fichadas estén en registros_reloj y que alguien deje el
 * latido que su proceso de faltas y tardanzas exige. Usa una conexión APARTE (usuario acotado);
 * la conexión de lectura de SiRex no se toca. Todas las horas son locales, como en esa base.
 */
class EscritorMitiv
{
    public function habilitado(): bool
    {
        return (bool) config('sirex.relojes_escribir_mitiv');
    }

    /**
     * Guarda las fichadas que todavía no están. Se considera duplicada la misma persona a la misma hora
     * original (igual que el lector viejo, sin mirar el reloj).
     *
     * @param  list<array{card_id: int, fecha_hora: string}>  $fichadas
     * @return int cantidad insertada
     */
    public function guardarFichadas(string $nombreReloj, array $fichadas): int
    {
        if ($fichadas === []) {
            return 0;
        }

        $db = $this->db();
        $existentes = [];
        $filasExistentes = $db->table('registros_reloj')
            ->where('fecha_hora_inmutable', '>=', min(array_column($fichadas, 'fecha_hora')))
            ->get(['card_id', 'fecha_hora_inmutable']);
        foreach ($filasExistentes as $fila) {
            $existentes[(int) $fila->card_id.'|'.substr((string) $fila->fecha_hora_inmutable, 0, 19)] = true;
        }

        $ahora = $this->ahora();
        $filas = [];
        foreach ($fichadas as $f) {
            $clave = $f['card_id'].'|'.$f['fecha_hora'];
            if (isset($existentes[$clave])) {
                continue;
            }
            $existentes[$clave] = true;
            $filas[] = [
                'card_id' => $f['card_id'],
                'fecha_hora' => $f['fecha_hora'],
                'fecha_hora_inmutable' => $f['fecha_hora'],
                'procesado' => false,
                'nombre_reloj' => $nombreReloj,
                'origen' => 'SIREX',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];
        }

        foreach (array_chunk($filas, 500) as $lote) {
            $db->table('registros_reloj')->insert($lote);
        }

        return count($filas);
    }

    /** Latido de "el lector funciona": el sistema viejo lo exige cada 30 minutos como máximo. Devuelve si escribió. */
    public function latido(): bool
    {
        $db = $this->db();
        $ultimo = $db->table('operaciones_reloj')->where('operacion', 'CHEQUEO_ESTADO_SERVICIO')->max('created_at');
        if ($ultimo !== null && CarbonImmutable::parse($ultimo)->diffInMinutes(Hora::ahora()) < (int) config('sirex.relojes_latido_minutos')) {
            return false;
        }

        $ahora = $this->ahora();
        $db->table('operaciones_reloj')->insert([
            'operacion' => 'CHEQUEO_ESTADO_SERVICIO', 'estado' => 'PROCESADO', 'created_at' => $ahora, 'updated_at' => $ahora,
        ]);

        return true;
    }

    /** El tablero de errores del sistema viejo muestra los CHEQUEO_RELOJES de los últimos 10 minutos con su log. */
    public function falla(string $ip): void
    {
        $db = $this->db();
        $ahora = $this->ahora();
        $operacion = $db->table('operaciones_reloj')->insertGetId([
            'operacion' => 'CHEQUEO_RELOJES', 'estado' => 'PROCESADO', 'created_at' => $ahora, 'updated_at' => $ahora,
        ]);
        $db->table('log_operaciones_reloj')->insert([
            'operacion_id' => $operacion, 'texto' => 'Reloj inaccesible: '.$ip, 'tipo' => 'ERROR_CHECKEO',
            'created_at' => $ahora, 'updated_at' => $ahora,
        ]);
    }

    private function db(): ConnectionInterface
    {
        return DB::connection((string) config('sirex.relojes_conexion_mitiv_escritura'));
    }

    private function ahora(): string
    {
        return Hora::ahora()->format('Y-m-d H:i:s');
    }
}
