<?php

namespace App\Services\Rrhh\Relojes;

/** Crea el cliente de un reloj. En las pruebas se reemplaza por relojes simulados. */
interface FabricaClienteReloj
{
    public function crear(string $ip, int $puerto): ClienteZk;
}
