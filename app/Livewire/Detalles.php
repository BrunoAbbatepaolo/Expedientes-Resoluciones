<?php

namespace App\Livewire;

use App\Models\Audit;
use App\Models\Expediente;
use App\Models\User;
use App\Services\ExpedientesIpv;
use App\Traits\AuthorizesOficina;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Detalles extends Component
{
    use AuthorizesOficina;

    #[Locked]
    public $id;

    public $expediente;

    public $pases = [];

    public function mount($id)
    {
        $this->id = $id;
        $this->expediente = Expediente::with([
            'pases.oficina',
            'pases.oficinaOrigen',
            'oficinaById',
        ])->findOrFail($id);
        $this->autorizarExpediente($this->expediente, 'expediente_ver');

        $this->pases = $this->expediente->pases->map(fn ($pase) => [
            'origen' => $pase->oficinaOrigen->nombre ?? 'Sin especificar',
            'destino' => $pase->oficina->nombre ?? 'Sin especificar',
            'fecha' => $pase->fecha,
            'hora' => $pase->hora,
            'observacion' => $pase->observacion,
            'importado' => $pase->importado,
        ])->toArray();
    }

    public function render(ExpedientesIpv $ipv)
    {
        $audits = Audit::where('auditable_type', Expediente::class)
            ->where('auditable_id', $this->expediente->id)
            ->latest()
            ->limit(30)
            ->get();
        $usuarios = User::whereIn('id', $audits->pluck('user_id')->filter()->unique())->get()->keyBy('id');

        $cambios = $audits->map(fn (Audit $a) => [
            'fecha' => $a->created_at,
            'usuario' => $usuarios->get($a->user_id)->name ?? 'Sistema',
            'evento' => ['created' => 'Registró', 'updated' => 'Editó', 'deleted' => 'Eliminó'][$a->event] ?? $a->event,
            'campos' => collect($a->new_values ?? [])
                ->except(['updated_at', 'created_at', 'id', 'deleted_at'])
                ->map(fn ($nuevo, $campo) => [
                    'campo' => str_replace('_', ' ', $campo),
                    'antes' => $a->old_values[$campo] ?? null,
                    'despues' => $nuevo,
                ])
                ->values(),
        ]);

        return view('livewire.detalles', [
            'cambios' => $cambios,
            'idIpv' => rescue(fn () => $ipv->idPorNumero($this->expediente->num_exp), null, false),
        ]);
    }
}
