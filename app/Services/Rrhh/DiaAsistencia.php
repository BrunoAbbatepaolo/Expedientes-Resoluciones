<?php

namespace App\Services\Rrhh;

use App\Enums\EstadoDia;
use Carbon\CarbonImmutable;

/** Resultado del cálculo de asistencia de un empleado en un día. */
final readonly class DiaAsistencia
{
    /**
     * @param  list<string>  $marcas  fichadas del día (H:i)
     */
    public function __construct(
        public CarbonImmutable $fecha,
        public EstadoDia $estado,
        public array $marcas = [],
        public int $minutosTarde = 0,
        public string $motivo = '',
        public ?string $horario = null,
    ) {}
}
