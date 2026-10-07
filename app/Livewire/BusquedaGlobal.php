<?php

namespace App\Livewire;

use App\Models\Expediente as ExpedienteOficina;
use App\Models\Resolucion;
use App\Services\ExpedientesIpv;
use App\Services\Rrhh\AlcanceFactory;
use App\Services\Rrhh\Contracts\Legajos;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Buscador del menú lateral: número de expediente o causante (IPV), registros de mi
 * oficina y resoluciones. Mínimo 3 caracteres; como mucho 5 resultados por grupo.
 */
class BusquedaGlobal extends Component
{
    public string $q = '';

    /**
     * @return array<string, Collection<int, array{titulo: string, detalle: ?string, url: string}>>
     */
    private function resultados(ExpedientesIpv $ipv): array
    {
        $q = trim($this->q);
        if (mb_strlen($q) < 3) {
            return [];
        }

        $user = auth()->user();
        /** @var array<string, Collection<int, array{titulo: string, detalle: ?string, url: string}>> $grupos */
        $grupos = [];
        $esc = addcslashes($q, '%_\\');

        if ($user->permiso('expediente_ver')) {
            $grupos['Expedientes del IPV'] = rescue(fn () => $ipv->buscarRapido($q, 5)
                ->map(fn (\stdClass $e): array => [
                    'titulo' => $e->numero,
                    'detalle' => trim($e->causante.' · '.($e->ubicacion ?? 'sin pases'), ' ·'),
                    'url' => route('expedientes.ipv.ficha', $e->id),
                ]), collect());

            $oficinaId = $user->oficinaAsignadaId();
            $grupos['Registrados en mi oficina'] = $oficinaId
                ? ExpedienteOficina::deOficina($oficinaId)
                    ->where(fn ($w) => $w->where('num_exp', 'like', $esc.'%')->orWhere('causante', 'like', '%'.$esc.'%'))
                    ->latest()->limit(5)->get()
                    ->map(fn ($e) => [
                        'titulo' => $e->num_exp,
                        'detalle' => $e->causante,
                        'url' => route('expedientes.detalle', $e->id),
                    ])
                : collect();
        }

        if ($user->permiso('resolucion_ver')) {
            $grupos['Resoluciones'] = Resolucion::query()
                ->where(fn ($w) => $w->where('numero_resolucion', 'like', $esc.'%')->orWhere('numero_exp', 'like', $esc.'%'))
                ->where(fn ($w) => $w->whereNull('oficina_id')->orWhere('oficina_id', $user->oficinaAsignadaId()))
                ->latest()->limit(5)->get()
                ->map(fn ($r) => [
                    'titulo' => 'Resolución '.($r->numero_resolucion ?? 's/n'),
                    'detalle' => 'Expediente '.$r->numero_exp,
                    'url' => route('resoluciones'),
                ]);
        }

        // Personal: solo RRHH y jefes, y dentro de su alcance.
        $grupos['Personal'] = rescue(function () use ($user, $q) {
            $alcance = app(AlcanceFactory::class)->para($user);

            return app(Legajos::class)->buscarRapido($alcance, $q, 5)->map(fn ($e) => [
                'titulo' => $e->apellido.', '.$e->nombre,
                'detalle' => 'Legajo '.$e->legajo.' · '.($e->oficinaRrhh->nombre ?? 'sin oficina'),
                'url' => route('rrhh.ficha', $e->id),
            ]);
        }, collect());

        return array_filter($grupos, fn ($g) => $g->isNotEmpty());
    }

    public function render(ExpedientesIpv $ipv): View
    {
        return view('livewire.busqueda-global', ['grupos' => $this->resultados($ipv)]);
    }
}
