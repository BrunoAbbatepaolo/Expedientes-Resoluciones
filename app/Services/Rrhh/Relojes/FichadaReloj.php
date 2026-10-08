<?php

namespace App\Services\Rrhh\Relojes;

/**
 * Una fichada tal como la guarda el reloj. La hora es la del reloj, sin zona (hora local).
 */
final readonly class FichadaReloj
{
    public function __construct(
        public string $userId,
        public string $fechaHora, // Y-m-d H:i:s
        public int $status,
        public int $punch,
    ) {}

    /** La tarjeta del empleado (empleados.card_id) es un entero: otros ids no se pueden asociar. */
    public function tarjeta(): ?int
    {
        return ctype_digit($this->userId) && strlen($this->userId) <= 9 ? (int) $this->userId : null;
    }
}
