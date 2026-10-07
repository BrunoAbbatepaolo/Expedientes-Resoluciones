<?php

namespace App\Providers;

use App\Services\Rrhh\AlcanceFactory;
use App\Services\Rrhh\CalculoAsistencia;
use App\Services\Rrhh\Organigrama;
use Illuminate\Support\ServiceProvider;

/**
 * Módulo RRHH: servicios por request y la fuente de datos (config sirex.rrhh_fuente).
 */
class RrhhServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Organigrama::class);
        $this->app->scoped(AlcanceFactory::class);
        $this->app->singleton(CalculoAsistencia::class, fn () => new CalculoAsistencia(
            toleranciaDefault: (int) config('sirex.rrhh_tolerancia_minutos'),
            tipoAusente: (int) config('sirex.rrhh_tipo_ausente_id'),
            tipoTarde: (int) config('sirex.rrhh_tipo_tarde_id'),
        ));
    }
}
