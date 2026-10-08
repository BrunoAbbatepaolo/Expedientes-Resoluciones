<?php

namespace App\Services\Rrhh\Relojes;

/** Canal de bytes con un reloj. En producción es TCP; en las pruebas, un reloj simulado en memoria. */
interface Transporte
{
    public function enviar(string $bytes): void;

    /** Devuelve exactamente $cantidad bytes o lanza ErrorReloj (tiempo agotado / conexión cerrada). */
    public function leer(int $cantidad): string;

    public function cerrar(): void;
}
