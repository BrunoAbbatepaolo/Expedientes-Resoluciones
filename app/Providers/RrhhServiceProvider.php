<?php

namespace App\Providers;

use App\Services\Rrhh\AlcanceFactory;
use App\Services\Rrhh\CalculoAsistencia;
use App\Services\Rrhh\Contracts;
use App\Services\Rrhh\Mitiv;
use App\Services\Rrhh\Organigrama;
use App\Services\Rrhh\Relojes\FabricaClienteReloj;
use App\Services\Rrhh\Relojes\FabricaClienteTcp;
use App\Services\Rrhh\Relojes\LectorRelojSirex;
use Illuminate\Support\ServiceProvider;
use LogicException;

/**
 * Módulo RRHH: servicios por request y la fuente de datos (config sirex.rrhh_fuente).
 */
class RrhhServiceProvider extends ServiceProvider
{
    /** Implementación de cada contrato con fuente 'mitiv' (fase 1, solo lectura). */
    private const MITIV = [
        Contracts\Legajos::class => Mitiv\LegajosMitiv::class,
        Contracts\Asistencias::class => Mitiv\AsistenciasMitiv::class,
        Contracts\Licencias::class => Mitiv\LicenciasMitiv::class,
        Contracts\Calendario::class => Mitiv\CalendarioMitiv::class,
        Contracts\LectorReloj::class => Mitiv\LectorRelojMitiv::class,
    ];

    public function register(): void
    {
        $this->app->scoped(Organigrama::class);
        $this->app->scoped(AlcanceFactory::class);
        $this->app->singleton(CalculoAsistencia::class, fn () => new CalculoAsistencia(
            toleranciaDefault: (int) config('sirex.rrhh_tolerancia_minutos'),
            tipoAusente: (int) config('sirex.rrhh_tipo_ausente_id'),
            tipoTarde: (int) config('sirex.rrhh_tipo_tarde_id'),
            limiteFalta: (int) config('sirex.rrhh_limite_falta_minutos'),
        ));

        $this->app->bind(FabricaClienteReloj::class, FabricaClienteTcp::class);

        foreach (self::MITIV as $contrato => $implementacion) {
            $this->app->scoped($contrato, function ($app) use ($contrato, $implementacion) {
                if (config('sirex.rrhh_fuente') !== 'mitiv') {
                    throw new LogicException('La fuente de RRHH "'.config('sirex.rrhh_fuente').'" llega en la fase 2; usá SIREX_RRHH_FUENTE=mitiv.');
                }

                // Con el agente de relojes de SiRex activo, el estado de los relojes sale de lo que él registra.
                if ($contrato === Contracts\LectorReloj::class && config('sirex.relojes_activo')) {
                    return $app->make(LectorRelojSirex::class);
                }

                return $app->make($implementacion);
            });
        }
    }
}
