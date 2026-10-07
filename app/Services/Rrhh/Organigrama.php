<?php

namespace App\Services\Rrhh;

use App\Models\Mitiv\OficinaRrhh;
use App\Models\RrhhOficinaPadre;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

/**
 * Dependencia entre las oficinas del catálogo RRHH (mitivdb.oficinas). La relación se
 * guarda en SiRex (rrhh_oficina_padres) porque el árbol de jerarquías del sistema viejo
 * no está cargado para las oficinas.
 */
class Organigrama
{
    /** @var array<int, int|null>|null */
    private ?array $padres = null;

    /** @return array<int, int|null> hijo => padre */
    public function padres(): array
    {
        return $this->padres ??= RrhhOficinaPadre::query()
            ->pluck('padre_mitiv_oficina_id', 'mitiv_oficina_id')
            ->map(fn ($p) => $p !== null ? (int) $p : null)
            ->all();
    }

    /**
     * Las oficinas indicadas y todas las que dependen de ellas, en cualquier nivel.
     * Tolera ciclos cargados a mano (cada oficina se visita una sola vez).
     *
     * @param  list<int>  $ids
     * @return list<int>
     */
    public function descendientes(array $ids): array
    {
        $hijos = [];
        foreach ($this->padres() as $hijo => $padre) {
            if ($padre !== null) {
                $hijos[$padre][] = $hijo;
            }
        }

        $visitadas = [];
        $pendientes = array_map('intval', $ids);
        while ($pendientes !== []) {
            $id = array_pop($pendientes);
            if (isset($visitadas[$id])) {
                continue;
            }
            $visitadas[$id] = true;
            array_push($pendientes, ...($hijos[$id] ?? []));
        }

        return array_keys($visitadas);
    }

    public function asignarPadre(int $oficina, ?int $padre): void
    {
        if ($padre !== null && in_array($padre, $this->descendientes([$oficina]), true)) {
            throw new InvalidArgumentException('Una oficina no puede depender de sí misma ni de una oficina que depende de ella.');
        }

        RrhhOficinaPadre::updateOrCreate(['mitiv_oficina_id' => $oficina], ['padre_mitiv_oficina_id' => $padre]);
        $this->padres = null;
    }

    /** Oficinas habilitadas del catálogo RRHH (cacheadas 1 hora). @return Collection<int, OficinaRrhh> */
    public function oficinas(): Collection
    {
        return Cache::remember('rrhh.oficinas', now()->addHour(), fn () => OficinaRrhh::habilitadas()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']));
    }

    /**
     * Oficinas en orden de árbol (raíces y debajo sus dependientes).
     *
     * @return list<array{oficina: OficinaRrhh, nivel: int}>
     */
    public function arbol(): array
    {
        $oficinas = $this->oficinas()->keyBy('id');
        $padres = $this->padres();
        $hijos = [];
        $raices = [];

        foreach ($oficinas as $id => $oficina) {
            $padre = $padres[$id] ?? null;
            if ($padre !== null && $oficinas->has($padre)) {
                $hijos[$padre][] = $id;
            } else {
                $raices[] = $id;
            }
        }

        $salida = [];
        $visitadas = [];
        $recorrer = function (int $id, int $nivel) use (&$recorrer, &$salida, &$visitadas, $hijos, $oficinas) {
            if (isset($visitadas[$id])) {
                return;
            }
            $visitadas[$id] = true;
            $salida[] = ['oficina' => $oficinas[$id], 'nivel' => $nivel];
            foreach ($hijos[$id] ?? [] as $hijo) {
                $recorrer($hijo, $nivel + 1);
            }
        };
        foreach ($raices as $id) {
            $recorrer($id, 0);
        }
        // Oficinas que solo forman parte de un ciclo (sin raíz): al final, para no perderlas.
        foreach ($oficinas->keys() as $id) {
            $recorrer($id, 0);
        }

        return $salida;
    }
}
