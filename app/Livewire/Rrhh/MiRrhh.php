<?php

namespace App\Livewire\Rrhh;

use App\Enums\EstadoDia;
use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Services\Rrhh\Contracts\Asistencias;
use App\Services\Rrhh\Contracts\Calendario;
use App\Services\Rrhh\Contracts\Legajos;
use App\Services\Rrhh\Contracts\Licencias;
use App\Services\Rrhh\Hora;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Lo propio de cada empleado: saldo de vacaciones, fichadas del mes, licencias, faltas
 * y sus datos. Se identifica por el legajo del usuario.
 */
#[Title('Mi RRHH')]
class MiRrhh extends Component
{
    use ConAlcance;

    #[Url]
    public string $mes = '';

    public function mount(): void
    {
        $this->mes = $this->mesValido($this->mes)->format('Y-m');
    }

    public function moverMes(int $delta): void
    {
        $this->mes = $this->mesValido($this->mes)->addMonths($delta)->format('Y-m');
    }

    public function render(Legajos $legajos, Asistencias $asistencias, Licencias $licencias, Calendario $calendario): View
    {
        $alcance = $this->alcance();
        $mes = $this->mesValido($this->mes);
        $hoy = Hora::hoy();
        $datos = ['mesActual' => $mes, 'empleado' => null, 'sinLegajo' => trim((string) auth()->user()->legajo) === ''];

        if ($alcance->empleadoId !== null) {
            $id = $alcance->empleadoId;
            $datos = array_merge($datos, $this->consultar(fn () => [
                'empleado' => $legajos->propio($alcance),
                'dias' => $asistencias->mes($alcance, $id, $mes->year, $mes->month),
                'saldo' => $licencias->saldo($alcance, $id),
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

    private function mesValido(string $mes): CarbonImmutable
    {
        return preg_match('/^\d{4}-\d{2}$/', $mes)
            ? CarbonImmutable::createFromFormat('!Y-m', $mes)
            : Hora::hoy()->startOfMonth();
    }
}
