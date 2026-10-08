<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Agente de relojes: solo corre con SIREX_RELOJES_ACTIVO=true. El servidor necesita ejecutar el
// planificador de Laravel (php artisan schedule:work, o una tarea programada cada minuto con schedule:run).
Schedule::command('relojes:sincronizar')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->runInBackground()
    ->when(fn () => (bool) config('sirex.relojes_activo'));

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
