<?php

namespace App\Livewire\Rrhh;

use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Livewire\Rrhh\Concerns\NavegaMeses;
use App\Services\Rrhh\Contracts\Asistencias;
use App\Services\Rrhh\Contracts\LectorReloj;
use App\Services\Rrhh\Hora;
use App\Services\Rrhh\Organigrama;
use App\Services\Rrhh\Relojes\SincronizadorRelojes;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Asistencia de todo el instituto (RRHH): hoy, grilla del mes, fichadas crudas y lector de relojes. */
#[Title('Asistencia')]
class Asistencia extends Component
{
    use ConAlcance;
    use NavegaMeses;
    use WithPagination;

    public const VISTAS = ['hoy' => 'Hoy', 'mes' => 'Mes por oficina', 'fichadas' => 'Fichadas'];

    #[Url]
    public string $vista = 'hoy';

    #[Url(as: 'oficina', except: null)]
    public ?int $oficinaId = null;

    #[Url(except: '')]
    public string $legajo = '';

    #[Url(except: '')]
    public string $reloj = '';

    #[Url(except: '')]
    public string $desde = '';

    #[Url(except: '')]
    public string $hasta = '';

    /** @var array{error: bool, texto: string}|null */
    public ?array $avisoSincronizacion = null;

    public function mount(): void
    {
        $this->exigir($this->alcance()->rrhh);

        if (! array_key_exists($this->vista, self::VISTAS)) {
            $this->vista = 'hoy';
        }
    }

    /** Lee los relojes ahora, sin esperar al planificador (cada 5 min). */
    public function sincronizarAhora(SincronizadorRelojes $sincronizador): void
    {
        $this->exigir($this->alcance()->rrhh);

        $resultados = $sincronizador->sincronizar();

        if ($resultados === []) {
            $this->avisoSincronizacion = ['error' => true, 'texto' => 'No hay relojes activos para sincronizar.'];

            return;
        }

        $fallaron = collect($resultados)->where('estado', 'error')->count();
        $nuevas = collect($resultados)->sum('nuevas');
        $texto = $nuevas.' '.($nuevas === 1 ? 'fichada nueva' : 'fichadas nuevas').' de '.count($resultados).' '.(count($resultados) === 1 ? 'reloj' : 'relojes').'.';

        $this->avisoSincronizacion = [
            'error' => $fallaron === count($resultados),
            'texto' => $fallaron > 0 ? $texto.' '.$fallaron.' sin respuesta.' : $texto,
        ];
    }

    public function updated(string $propiedad): void
    {
        $this->resetPage();
    }

    public function render(Asistencias $asistencias, LectorReloj $lector, Organigrama $organigrama): View
    {
        $alcance = $this->alcance();
        $oficinas = $this->consultar(fn () => $organigrama->oficinas(), collect());
        $lectorEstado = $this->consultar(fn () => $lector->estado(Hora::ahora()));
        $mes = $this->mesActual();
        $datos = [];

        if (! $this->errorFuente) {
            $datos = $this->consultar(fn () => match ($this->vista) {
                'mes' => ['grilla' => $asistencias->grilla($alcance, $mes->year, $mes->month, $this->oficinaId ?? (int) $oficinas->first()?->id)],
                'fichadas' => [
                    'fichadas' => $asistencias->fichadas($alcance, [
                        'legajo' => ctype_digit($this->legajo) ? $this->legajo : null,
                        'reloj' => $this->reloj ?: null,
                        'desde' => $this->desde ?: null,
                        'hasta' => $this->hasta ?: null,
                    ]),
                    'relojes' => $asistencias->relojes(),
                ],
                default => ['hoy' => $asistencias->dia($alcance, Hora::hoy(), $this->oficinaId)],
            }, []);
        }

        return view('livewire.rrhh.asistencia', $datos + [
            'oficinas' => $oficinas,
            'lector' => $lectorEstado,
            'mesActual' => $mes,
            'error' => $this->errorFuente,
        ]);
    }
}
