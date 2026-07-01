<?php
// app/Livewire/DashboardPanel.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Expediente;
use App\Models\Resolucion;
use Illuminate\Support\Facades\Auth;

class DashboardPanel extends Component
{
    public int $oficinaId;

    public function mount(): void
    {
        $this->oficinaId = Auth::user()->oficinaAsignadaId() ?? 0;
    }

    public function render()
    {
        $oficina = $this->oficinaId;

        // Métricas del mes actual
        $mesInicio = now()->startOfMonth();
        $mesFin    = now()->endOfMonth();

        $activos    = Expediente::deOficina($oficina)
                        ->whereNull('fecha_salida')
                        ->whereNull('deleted_at')
                        ->count();

        $egresados  = Expediente::deOficina($oficina)
                        ->whereNotNull('fecha_salida')
                        ->whereBetween('fecha_salida', [$mesInicio, $mesFin])
                        ->whereNull('deleted_at')
                        ->count();

        $ingresados = Expediente::deOficina($oficina)
                        ->whereBetween('fecha_ingreso', [$mesInicio, $mesFin])
                        ->whereNull('deleted_at')
                        ->count();

        $resoluciones = Resolucion::whereBetween('fecha', [$mesInicio, $mesFin])
                        ->whereNull('deleted_at')
                        ->count();

        // Últimos 5 expedientes ingresados a la oficina
        $recientes = Expediente::deOficina($oficina)
                        ->whereNull('deleted_at')
                        ->with('oficinaById')
                        ->orderByDesc('fecha_ingreso')
                        ->limit(5)
                        ->get();

        return view('livewire.dashboard-panel', compact(
            'activos', 'egresados', 'ingresados', 'resoluciones', 'recientes'
        ));
    }
}