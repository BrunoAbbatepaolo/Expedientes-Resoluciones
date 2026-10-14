<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Sin <link rel="preload">: el CSS ya se carga como stylesheet y con wire:navigate
        // el preload duplicado solo generaba advertencias en la consola.
        Vite::usePreloadTagAttributes(false);

        // Tope general de pedidos (incluye los de Livewire): por usuario si hay sesión, por IP si no.
        // Los usuarios con sesión tienen más margen porque la búsqueda en vivo y los refrescos automáticos
        // hacen muchos pedidos chicos. Configurable con SIREX_LIMITE_PETICIONES.
        RateLimiter::for('global', function (Request $request) {
            $limite = (int) config('sirex.limite_peticiones');

            return $request->user()
                ? Limit::perMinute($limite)->by('u'.$request->user()->getAuthIdentifier())
                : Limit::perMinute(max(1, intdiv($limite, 3)))->by('ip'.$request->ip());
        });

        // Política de contraseñas (cambio de clave, clave inicial y restablecimiento): 8 caracteres como
        // mínimo, con mayúsculas, minúsculas y números. El símbolo se recomienda pero no se exige.
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        // Ingresos y fallos de ingreso quedan en la auditoría (App\Listeners\RegistrarAcceso, que Laravel
        // registra solo por su tipo de evento: registrarlo acá también lo duplicaría).

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
