<?php

namespace Tests\Support;

use App\Services\Rrhh\Relojes\ClienteZk;
use App\Services\Rrhh\Relojes\ErrorReloj;
use App\Services\Rrhh\Relojes\FabricaClienteReloj;

/** Entrega relojes simulados por IP; un ErrorReloj en lugar del reloj simula que no responde. */
class FabricaRelojesSimulada implements FabricaClienteReloj
{
    /** @var array<string, RelojSimulado|ErrorReloj> */
    public array $relojes = [];

    public function crear(string $ip, int $puerto): ClienteZk
    {
        $reloj = $this->relojes[$ip] ?? new ErrorReloj("No se pudo conectar con el reloj {$ip}:{$puerto}.");
        if ($reloj instanceof ErrorReloj) {
            throw $reloj;
        }

        return new ClienteZk($reloj);
    }
}
