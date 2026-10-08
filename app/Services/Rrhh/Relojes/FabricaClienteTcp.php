<?php

namespace App\Services\Rrhh\Relojes;

final class FabricaClienteTcp implements FabricaClienteReloj
{
    public function crear(string $ip, int $puerto): ClienteZk
    {
        return new ClienteZk(
            new TransporteTcp($ip, $puerto, (int) config('sirex.relojes_timeout')),
            (int) config('sirex.relojes_clave'),
        );
    }
}
