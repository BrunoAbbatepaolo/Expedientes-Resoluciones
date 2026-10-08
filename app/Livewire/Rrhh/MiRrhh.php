<?php

namespace App\Livewire\Rrhh;

use App\Enums\EstadoDia;
use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Livewire\Rrhh\Concerns\NavegaMeses;
use App\Services\Rrhh\Contracts\Asistencias;
use App\Services\Rrhh\Contracts\Calendario;
use App\Services\Rrhh\Contracts\Legajos;
use App\Services\Rrhh\Contracts\Licencias;
use App\Services\Rrhh\Hora;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Lo propio de cada empleado: saldo de vacaciones, fichadas del mes, licencias, faltas
 * y sus datos. Se identifica por el legajo del usuario.
 */
#[Title('Mi RRHH')]
class MiRrhh extends Component
{
    use ConAlcance;
    use NavegaMeses;

    public function render(Legajos $legajos, Asistencias $asistencias, Licencias $licencias, Calendario $calendario): View
    {
        $alcance = $this->alcance();
        $mes = $this->mesActual();
        $hoy = Hora::hoy();
        $datos = ['mesActual' => $mes, 'empleado' => null, 'sinLegajo' => trim((string) auth()->user()->legajo) === ''];

        if ($alcance->empleadoId !== null) {
            $id = $alcance->empleadoId;
            $datos = array_merge($datos, $this->consultar(fn () => [
                'empleado' => $legajos->propio($alcance),
                'dias' => $asistencias->mes($alcance, $id, $mes->year, $mes->month),
                'vacaciones' => $licencias->vacaciones($alcance, $id),
                'licencias' => $licencias->deEmpleado($alcance, $id, $mes->year),
                'inasistencias' => $licencias->inasistenciasDe($alcance, $id, $mes->year),
                'proxima' => $licencias->proxima($alcance, $id, $hoy),
                'feriado' => $calendario->proximo($hoy),
            ], []));
        }

        $dias = collect($datos['dias'] ?? []);

        return view('livewire.rrhh.mi-rrhh', $datos + [
            'dias' => $dias->all(),
            'faltas' => $dias->where('estado', EstadoDia::Falta)->count(),
            'tardes' => $dias->where('estado', EstadoDia::Tarde)->count(),
            'error' => $this->errorFuente,
        ]);
    }
}
