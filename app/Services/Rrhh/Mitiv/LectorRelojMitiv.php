<?php

namespace App\Services\Rrhh\Mitiv;

use App\Models\Mitiv\OperacionReloj;
use App\Models\Mitiv\RegistroReloj;
use App\Services\Rrhh\Contracts\Calendario;
use App\Services\Rrhh\Contracts\LectorReloj;
use Carbon\CarbonImmutable;

/**
 * El lector de relojes deja un latido (CHEQUEO_ESTADO_SERVICIO) cada 30 minutos en
 * operaciones_reloj y las fichadas en registros_reloj. Solo se avisa en horario laboral
 * (fuera de horario el programa puede estar apagado a propósito) y con un margen mayor que
 * el período del latido: en 90 días de datos reales hubo cortes normales de hasta 60 min.
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

        $inicio = $ahora->startOfDay()->setTime(7, 0);
        $laboral = $ahora->isWeekday() && $ahora->gte($inicio) && $ahora->hour < 15
            && ! isset($this->calendario->dias($ahora, $ahora)[$ahora->toDateString()]);

        // Al empezar la jornada el último dato es de ayer: se mide desde las 7 para dar margen a abrir el programa.
        $sinLatido = $laboral && ($chequeo === null || max($chequeo, $inicio)->diffInMinutes($ahora) > $limite);
        $sinFichadas = $laboral && ($fichada === null || max($fichada, $inicio)->diffInMinutes($ahora) > $limite);

        $mensaje = match (true) {
            $sinLatido => 'El lector de relojes no responde desde '.($chequeo?->format('d/m H:i') ?? 'hace mucho').'. Las fichadas no se están cargando: hay que abrir el programa de los relojes.',
            $sinFichadas => 'No entran fichadas desde '.($fichada?->format('d/m H:i') ?? 'hace mucho').' en horario laboral.',
            default => '',
        };

        return ['ultima_fichada' => $fichada, 'ultimo_chequeo' => $chequeo, 'alerta' => $mensaje !== '', 'mensaje' => $mensaje];
    }
}
