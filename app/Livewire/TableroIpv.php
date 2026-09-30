<?php

namespace App\Livewire;

use App\Models\Seguimiento;
use App\Services\ExpedientesIpv;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Tablero del dashboard: expedientes que hoy están en mi oficina según el IPV,
 * los más demorados y los expedientes que sigo (avisando si se movieron).
 * Se carga diferido (#[Lazy]) para que el dashboard aparezca al instante.
 */
#[Lazy]
class TableroIpv extends Component
{
    public function placeholder(): View
    {
        return view('livewire.tablero-ipv-placeholder');
    }

    public function marcarVisto(int $seguimientoId, ExpedientesIpv $ipv): void
    {
        $seguimiento = Seguimiento::where('user_id', auth()->id())->findOrFail($seguimientoId);
        $ubicacion = rescue(fn () => $ipv->ubicaciones([$seguimiento->numero_exp])->get($seguimiento->numero_exp));

        $seguimiento->update(['ultimo_pase_visto' => $ubicacion?->ubicacion_desde]);
    }

    public function dejarDeSeguir(int $seguimientoId): void
    {
        Seguimiento::where('user_id', auth()->id())->whereKey($seguimientoId)->delete();
    }

    public function render(ExpedientesIpv $ipv): View
    {
        $diasDemora = config('sirex.dias_demora');
        $error = false;
        $oficinaIpv = null;
        $resumen = null;
        $demorados = collect();
        $seguimientos = Seguimiento::where('user_id', auth()->id())->latest()->get();
        $ubicaciones = collect();

        try {
            $oficinaIpv = auth()->user()->permiso('expediente_ver') ? $ipv->oficinaIpvDe(auth()->user()) : null;
            if ($oficinaIpv) {
                $resumen = $ipv->resumenOficina($oficinaIpv, $diasDemora);
                $demorados = $ipv->enOficina($oficinaIpv, $diasDemora, 6);
            }
            $ubicaciones = $ipv->ubicaciones($seguimientos->pluck('numero_exp')->all());
        } catch (\Throwable $e) {
            report($e);
            $error = true;
        }

        $seguimientos->each(function (Seguimiento $s) use ($ubicaciones) {
            $u = $ubicaciones->get($s->numero_exp);
            $s->setAttribute('ipv', $u);
            $s->setAttribute('se_movio', $u && $u->ubicacion_desde
                && (! $s->ultimo_pase_visto || Carbon::parse($u->ubicacion_desde)->gt($s->ultimo_pase_visto)));
        });

        return view('livewire.tablero-ipv', [
            'error' => $error,
            'oficinaIpvId' => $oficinaIpv,
            'oficinaIpv' => $oficinaIpv ? rescue(fn () => $ipv->oficinas()->firstWhere('id', $oficinaIpv)?->nombre) : null,
            'resumen' => $resumen,
            'demorados' => $demorados,
            'seguimientos' => $seguimientos->sortByDesc('se_movio')->values(),
            'diasDemora' => $diasDemora,
        ]);
    }
}
