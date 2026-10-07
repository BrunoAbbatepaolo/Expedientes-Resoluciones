<?php

namespace App\Services\Rrhh\Contracts;

use Carbon\CarbonImmutable;

/** Salud del servicio que carga las fichadas de los relojes. */
interface LectorReloj
{
    /**
     * @return array{ultima_fichada: ?CarbonImmutable, ultimo_chequeo: ?CarbonImmutable, alerta: bool, mensaje: string}
     */
    public function estado(CarbonImmutable $ahora): array;
}
