<?php

namespace App\Services\Rrhh;

use Carbon\CarbonImmutable;

/**
 * "Ahora" en hora local del IPV. La base vieja guarda fichadas y fechas sin zona horaria
 * (hora de Tucumán), y la app puede correr en UTC: para compararlas, ahora se arma con
 * la hora local de pared, en la zona de la app.
 */
final class Hora
{
    public static function ahora(): CarbonImmutable
    {
        $local = CarbonImmutable::now(config('sirex.rrhh_zona_horaria'));

        return CarbonImmutable::parse($local->format('Y-m-d H:i:s'));
    }

    public static function hoy(): CarbonImmutable
    {
        return self::ahora()->startOfDay();
    }
}
