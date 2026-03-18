<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Expediente;
use App\Models\Oficina;
use Carbon\Carbon;

class Detalles extends Component
{
    public $id;
    public $expediente;
    public $pases;

    public function mount($id)
    {
        $this->id = $id;
        $this->expediente = Expediente::with([
            'pases.oficina',
            'pases.oficinaOrigen',
            'oficinaById'
        ])->findOrFail($id);

        // Pases importados — origen y destino ya están guardados
        $pasesHistoricos = $this->expediente->pases
            ->sortBy(fn($p) => $p->fecha->format('Y-m-d') . ' ' . ($p->hora ?? '00:00:00'))
            ->values()
            ->map(fn($p) => [
                'origen'     => $p->oficinaOrigen->nombre ?? 'Desconocido',
                'destino'    => $p->oficina->nombre ?? 'Desconocido',
                'fecha'      => $p->fecha,
                'hora'       => $p->hora,
                'observacion'=> $p->observacion,
                'importado'  => $p->importado,
                'firmado'    => $p->firmado,
            ]);

        // Pase de ingreso a tu oficina
        $ultimoPase = $this->expediente->pases
            ->sortByDesc(fn($p) => $p->fecha->format('Y-m-d') . ' ' . ($p->hora ?? '00:00:00'))
            ->first();

        $paseIngreso = [
            'origen'     => $ultimoPase?->oficina->nombre ?? 'Desconocido',
            'destino'    => $this->expediente->oficinaById->nombre ?? 'Oficina desconocida',
            'fecha'      => Carbon::parse($this->expediente->fecha_ingreso),
            'hora'       => null,
            'observacion'=> 'Ingreso',
            'importado'  => false,
            'firmado'    => false,
        ];

        // Pase de salida si existe
        $paseSalida = null;
        if ($this->expediente->fecha_salida && $this->expediente->ofi_salida) {
            $oficinaSalida = Oficina::find($this->expediente->ofi_salida);
            $paseSalida = [
                'origen'     => $this->expediente->oficinaById->nombre ?? 'Desconocido',
                'destino'    => $oficinaSalida->nombre ?? 'Oficina desconocida',
                'fecha'      => Carbon::parse($this->expediente->fecha_salida),
                'hora'       => null,
                'observacion'=> 'Salida',
                'importado'  => false,
                'firmado'    => false,
            ];
        }

        // Unir y ordenar de más nuevo a más viejo
        $todos = $pasesHistoricos->push($paseIngreso);
        if ($paseSalida) $todos->push($paseSalida);

        $ordenados = $todos
            ->sortByDesc(fn($p) => $p['fecha']->format('Y-m-d') . ' ' . ($p['hora'] ?? '00:00:00'))
            ->values();

        // Ingreso siempre primero si no hay salida
        if (!$paseSalida) {
            $ingreso = $ordenados->firstWhere('observacion', 'Ingreso');
            $this->pases = $ordenados
                ->reject(fn($p) => $p['observacion'] === 'Ingreso')
                ->prepend($ingreso)
                ->values();
        } else {
            $this->pases = $ordenados;
        }
    }

    public function render()
    {
        return view('livewire.detalles');
    }
}