<?php

namespace App\Providers;

use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Sin <link rel="preload">: el CSS ya se carga como stylesheet y con wire:navigate
        // el preload duplicado solo generaba advertencias en la consola.
        Vite::usePreloadTagAttributes(false);

        // mitivdb es la base del sistema viejo, que sigue en producción: SiRex solo la
        // consulta. Cada sesión se abre en modo solo lectura para que ningún error de
        // código pueda modificarla (además del bloqueo en App\Models\Mitiv\MitivModel).
        Event::listen(function (ConnectionEstablished $event) {
            if ($event->connectionName === 'pgsql_mitiv' && $event->connection->getDriverName() === 'pgsql') {
                $event->connection->statement('SET SESSION CHARACTERISTICS AS TRANSACTION READ ONLY');
            }
        });
    }
}
