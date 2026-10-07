<?php

namespace App\Livewire\Rrhh;

use App\Enums\NivelDatoRrhh;
use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Livewire\Rrhh\Concerns\NavegaMeses;
use App\Services\Rrhh\Contracts\Asistencias;
use App\Services\Rrhh\Contracts\Legajos;
use App\Services\Rrhh\Contracts\Licencias;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Ficha de un empleado con pestañas. Cada pestaña muestra lo que el nivel del usuario
 * permite (jefe: básico; RRHH: personal; jefa de RRHH o el propio: sensible).
 */
#[Title('Ficha del empleado')]
class FichaEmpleado extends Component
{
    use ConAlcance;
    use NavegaMeses;

    public const PESTANIAS = [
        'datos' => ['Datos', NivelDatoRrhh::Basico],
        'familia' => ['Familia', NivelDatoRrhh::Personal],
        'trayectoria' => ['Trayectoria', NivelDatoRrhh::Personal],
        'asistencia' => ['Asistencia', NivelDatoRrhh::Basico],
        'licencias' => ['Licencias', NivelDatoRrhh::Basico],
        'inasistencias' => ['Inasistencias', NivelDatoRrhh::Basico],
        'archivos' => ['Archivos', NivelDatoRrhh::Personal],
    ];

    #[Locked]
    public int $empleado;

    #[Url]
    public string $pestania = 'datos';

    public function mount(int $empleado, Legajos $legajos): void
    {
        $this->empleado = $empleado;
        // Autoriza (403/404) y registra el acceso una sola vez, al abrir la ficha.
        $this->consultar(fn () => $legajos->ficha($this->alcance(), $empleado));

        if (! array_key_exists($this->pestania, self::PESTANIAS)) {
            $this->pestania = 'datos';
        }
    }

    public function render(Legajos $legajos, Asistencias $asistencias, Licencias $licencias): View
    {
        $alcance = $this->alcance();
        $mes = $this->mesActual();
        $ficha = $this->consultar(fn () => $legajos->ficha($alcance, $this->empleado, registrarAcceso: false));

        $extra = [];
        if ($ficha) {
            $extra = $this->consultar(fn () => match ($this->pestania) {
                'asistencia' => ['dias' => $asistencias->mes($alcance, $this->empleado, $mes->year, $mes->month)],
                'licencias' => [
                    'licencias' => $licencias->deEmpleado($alcance, $this->empleado, $mes->year),
                    'saldo' => $licencias->saldo($alcance, $this->empleado),
                ],
                'inasistencias' => ['inasistencias' => $licencias->inasistenciasDe($alcance, $this->empleado, $mes->year)],
                default => [],
            }, []);
        }

        $nivel = $ficha['nivel'] ?? NivelDatoRrhh::Basico;
        $pestanias = array_filter(self::PESTANIAS, fn ($p) => $nivel->incluye($p[1]));
        if (! isset($pestanias[$this->pestania])) {
            $this->pestania = 'datos';
        }

        return view('livewire.rrhh.ficha-empleado', [
            'ficha' => $ficha,
            'pestanias' => $pestanias,
            'mesActual' => $mes,
            'puedeVolver' => $alcance->rrhh || $alcance->esJefe(),
            'error' => $this->errorFuente,
        ] + $extra);
    }
}
