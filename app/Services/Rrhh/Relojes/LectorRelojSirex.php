<?php

namespace App\Services\Rrhh\Relojes;

use App\Models\RrhhReloj;
use App\Services\Rrhh\Contracts\Calendario;
use App\Services\Rrhh\Contracts\LectorReloj;
use App\Services\Rrhh\Hora;
use Carbon\CarbonImmutable;

/**
 * Estado del lector de relojes cuando el agente de SiRex está activo: se mide cada reloj con lo que
 * el propio agente registra (último contacto, hora del reloj, fallas). Solo se alerta en horario
 * laboral y con un margen mayor que el período de la sincronización.
 */
class LectorRelojSirex implements LectorReloj
{
    public function __construct(private Calendario $calendario) {}

    public function estado(CarbonImmutable $ahora): array
    {
        $limite = (int) config('sirex.rrhh_reloj_alerta_minutos');
        $desvioMax = (int) config('sirex.relojes_desvio_max_segundos');

        $inicio = $ahora->startOfDay()->setTime(7, 0);
        $laboral = $ahora->isWeekday() && $ahora->gte($inicio) && $ahora->hour < 15
            && ! isset($this->calendario->dias($ahora, $ahora)[$ahora->toDateString()]);

        $relojes = [];
        $problemas = [];
        $ultimoContacto = null;
        $ultimaFichada = null;

        foreach (RrhhReloj::where('activo', true)->orderBy('nombre')->get() as $reloj) {
            $contacto = $reloj->ultimo_contacto_at ? Hora::local($reloj->ultimo_contacto_at) : null;
            $fichada = $reloj->ultima_fichada_at ? CarbonImmutable::parse($reloj->ultima_fichada_at) : null;
            $ultimoContacto = $contacto && (! $ultimoContacto || $contacto->gt($ultimoContacto)) ? $contacto : $ultimoContacto;
            $ultimaFichada = $fichada && (! $ultimaFichada || $fichada->gt($ultimaFichada)) ? $fichada : $ultimaFichada;

            $estado = 'ok';
            $sinContacto = $laboral && ($contacto === null || max($contacto, $inicio)->diffInMinutes($ahora) > $limite);
            if ($sinContacto) {
                $estado = 'sin_contacto';
                $problemas[] = "El reloj {$reloj->nombre} ({$reloj->ip}) no responde".($contacto ? ' desde '.$contacto->format('d/m H:i') : '').'. Sus fichadas no se están cargando.';
            } elseif ($reloj->desvio_segundos !== null && abs($reloj->desvio_segundos) > $desvioMax) {
                $estado = 'hora_desviada';
                $minutos = round(abs($reloj->desvio_segundos) / 60);
                $problemas[] = "El reloj {$reloj->nombre} tiene la hora {$minutos} min ".($reloj->desvio_segundos > 0 ? 'adelantada' : 'atrasada').': las fichadas salen corridas.';
            }

            $relojes[] = [
                'id' => $reloj->id, 'nombre' => $reloj->nombre, 'ip' => $reloj->ip, 'tipo' => $reloj->tipo, 'estado' => $estado,
                'modelo' => $reloj->modelo, 'ultimo_contacto' => $contacto, 'ultima_fichada' => $fichada,
                'registros' => $reloj->registros_en_reloj, 'desvio_segundos' => $reloj->desvio_segundos,
                'fallas_seguidas' => $reloj->fallas_seguidas, 'ultimo_error' => $reloj->ultimo_error,
            ];
        }

        if ($relojes === []) {
            $problemas[] = 'El agente de relojes está activo pero no hay relojes cargados (php artisan relojes:agregar).';
        }

        return [
            'ultima_fichada' => $ultimaFichada, 'ultimo_chequeo' => $ultimoContacto,
            'alerta' => $problemas !== [], 'mensaje' => implode(' ', $problemas), 'relojes' => $relojes,
        ];
    }
}
