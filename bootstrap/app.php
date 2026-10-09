<?php

use App\Http\Middleware\CabecerasDeSeguridad;
use App\Http\Middleware\EnsurePasswordIsChanged;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'password.change' => EnsurePasswordIsChanged::class,
        ]);

        // Cabeceras de seguridad y tope general de pedidos (ver RateLimiter 'global' en AppServiceProvider).
        $middleware->web(append: [
            CabecerasDeSeguridad::class,
            'throttle:global',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
