<?php

namespace App\Services\Rrhh\Mitiv;

use App\Models\Mitiv\OperacionReloj;
use App\Models\Mitiv\RegistroReloj;
use App\Services\Rrhh\Contracts\Calendario;
use App\Services\Rrhh\Contracts\LectorReloj;
use Carbon\CarbonImmutable;

/**
 * El lector de relojes deja un latido (CHEQUEO_ESTADO_SERVICIO) en operaciones_reloj y las
 * fichadas en registros_reloj. Se avisa si el latido se cortó o si en horario laboral
 * no entran fichadas.
 */
class LectorRelojMitiv implements LectorReloj
{
    public function __construct(private Calendario $calendario) {}

    public function estado(CarbonImmutable $ahora): array
    {
        $limite = (int) config('sirex.rrhh_reloj_alerta_minutos');

        // Por id (clave primaria) y no por fecha: registros_reloj tiene 1,5 M de filas.
        $fichada = RegistroReloj::orderByDesc('id')->value('fecha_hora');
        $chequeo = OperacionReloj::where('operacion', 'CHEQUEO_ESTADO_SERVICIO')->orderByDesc('id')->value('created_at');
        $fichada = $fichada ? CarbonImmutable::parse($fichada) : null;
        $chequeo = $chequeo ? CarbonImmutable::parse($chequeo) : null;

        $laboral = $ahora->isWeekday() && $ahora->hour >= 7 && $ahora->hour < 15
            && ! isset($this->calendario->dias($ahora, $ahora)[$ahora->toDateString()]);

        $mensaje = match (true) {
            $chequeo === null || $chequeo->diffInMinutes($ahora) > $limite => 'El lector de relojes no responde desde '.($chequeo?->format('d/m H:i') ?? 'hace mucho').'. Las fichadas no se están cargando: hay que abrir el programa de los relojes.',
            $laboral && ($fichada === null || $fichada->diffInMinutes($ahora) > $limite) => 'No entran fichadas desde '.($fichada?->format('d/m H:i') ?? 'hace mucho').' en horario laboral.',
            default => '',
        };

        return ['ultima_fichada' => $fichada, 'ultimo_chequeo' => $chequeo, 'alerta' => $mensaje !== '', 'mensaje' => $mensaje];
    }
}
