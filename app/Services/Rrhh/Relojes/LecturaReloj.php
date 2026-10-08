<?php

namespace App\Services\Rrhh\Relojes;

/** Resultado de leer la memoria de fichadas de un reloj. */
final readonly class LecturaReloj
{
    /**
     * @param  list<FichadaReloj>  $fichadas
     * @param  int  $descartadas  registros con fecha inválida (no se pueden interpretar)
     */
    public function __construct(
        public array $fichadas,
        public int $descartadas,
        public int $bytesPorRegistro,
    ) {}
}
