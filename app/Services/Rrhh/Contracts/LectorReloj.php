<?php

namespace App\Services\Rrhh\Contracts;

use Carbon\CarbonImmutable;

/** Salud del servicio que carga las fichadas de los relojes. */
interface LectorReloj
{
    /**
     * Con el agente de SiRex activo se suma `relojes`: el estado de cada reloj.
     *
     * @return array{ultima_fichada: ?CarbonImmutable, ultimo_chequeo: ?CarbonImmutable, alerta: bool, mensaje: string, relojes?: list<array<string, mixed>>}
     */
    public function estado(CarbonImmutable $ahora): array;
}
