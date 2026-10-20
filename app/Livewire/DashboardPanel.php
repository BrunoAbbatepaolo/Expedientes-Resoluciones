<?php

// app/Livewire/DashboardPanel.php

namespace App\Livewire;

use App\Models\Expediente;
use App\Models\Resolucion;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DashboardPanel extends Component
{
    // Locked: si el cliente pudiera cambiarlo vería métricas de otra oficina.
    #[Locked]
    public int $oficinaId;

    public function mount(): void
    {
        $this->oficinaId = Auth::user()->oficinaAsignadaId() ?? 0;
    }

    public function render()
    {
        $oficina = $this->oficinaId;
        $usuario = Auth::user();

        // Cada bloque del panel responde a un permiso: sin él no se muestra ni se consulta.
        $verExpedientes = (bool) $usuario->permiso('expediente_ver');
        $verResoluciones = (bool) $usuario->permiso('resolucion_ver');

        $activos = $egresados = $ingresados = $resoluciones = 0;
        $recientes = collect();

        // Métricas del mes actual
        $mesInicio = now()->startOfMonth();
        $mesFin = now()->endOfMonth();

        if ($verExpedientes) {
            $activos = Expediente::deOficina($oficina)
                ->whereNull('fecha_salida')
                ->whereNull('deleted_at')
                ->count();

            $egresados = Expediente::deOficina($oficina)
                ->whereNotNull('fecha_salida')
                ->whereBetween('fecha_salida', [$mesInicio, $mesFin])
                ->whereNull('deleted_at')
                ->count();

            $ingresados = Expediente::deOficina($oficina)
                ->whereBetween('fecha_ingreso', [$mesInicio, $mesFin])
                ->whereNull('deleted_at')
                ->count();

            // Últimos 5 expedientes ingresados a la oficina
            $recientes = Expediente::deOficina($oficina)
                ->whereNull('deleted_at')
                ->with('oficinaById')
                ->orderByDesc('fecha_ingreso')
                ->limit(5)
                ->get();
        }

        if ($verResoluciones) {
            $resoluciones = Resolucion::whereBetween('fecha', [$mesInicio, $mesFin])
                ->whereNull('deleted_at')
                ->count();
        }

        return view('livewire.dashboard-panel', compact(
            'activos', 'egresados', 'ingresados', 'resoluciones', 'recientes', 'verExpedientes', 'verResoluciones'
        ));
    }
}
