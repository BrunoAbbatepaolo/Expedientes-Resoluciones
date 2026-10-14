<?php

namespace App\Services\Resoluciones;

use App\Models\Resolucion;
use App\Services\ExpedientesIpv;
use Illuminate\Support\Collection;

/**
 * Resoluciones cuyo número de expediente no existe en mitivdb (cargadas antes de exigir el vínculo, o con
 * un número mal tipeado). Se comprueba por lotes: una consulta a mitivdb cada 500 números, no una por resolución.
 * Es solo un informe: no se corrige ni se borra nada automáticamente.
 */
class SinExpediente
{
    private const LOTE = 500;

    public function __construct(private ExpedientesIpv $ipv) {}

    /** @return Collection<int, Resolucion> */
    public function buscar(int $lote = self::LOTE): Collection
    {
        $huerfanas = collect();

        Resolucion::query()
            ->with('oficina:id,nombre')
            ->orderBy('id')
            ->chunkById($lote, function (Collection $grupo) use ($huerfanas) {
                $existen = array_flip($this->ipv->numerosExistentes($grupo->pluck('numero_exp')->filter()->map(fn ($n) => trim((string) $n))->unique()->values()->all()));

                foreach ($grupo as $resolucion) {
                    if (! isset($existen[trim((string) $resolucion->numero_exp)])) {
                        $huerfanas->push($resolucion);
                    }
                }
            });

        return $huerfanas->values();
    }
}
