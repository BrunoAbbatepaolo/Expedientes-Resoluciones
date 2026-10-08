<?php

namespace App\Livewire\Rrhh;

use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Services\Rrhh\Contracts\Licencias as LicenciasRrhh;
use App\Services\Rrhh\Organigrama;
use App\Services\Rrhh\PedidosLicencia;
use App\Traits\NotificaUi;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Licencias de todo el personal, vacaciones adeudadas y pedidos de licencia (RRHH). */
#[Title('Licencias')]
class Licencias extends Component
{
    use ConAlcance;
    use NotificaUi;
    use WithPagination;

    public const VISTAS = ['listado' => 'Licencias', 'adeudadas' => 'Vacaciones adeudadas'];

    #[Url]
    public string $vista = 'listado';

    #[Url(as: 'q', except: '')]
    public string $texto = '';

    #[Url(as: 'tipo', except: null)]
    public ?int $tipoId = null;

    #[Url(as: 'oficina', except: null)]
    public ?int $oficinaId = null;

    #[Url(except: '')]
    public string $desde = '';

    #[Url(except: '')]
    public string $hasta = '';

    /** Motivo de rechazo en edición (id del pedido => texto). */
    public array $motivos = [];

    /** Formulario "tipo de licencia que se puede pedir". */
    public ?int $tipoPedibleId = null;

    public bool $tipoRetroactivo = false;

    public bool $tipoAdjunto = false;

    /** @return array<string, string> */
    public static function vistas(bool $confirma): array
    {
        if (! PedidosLicencia::habilitado() || ! $confirma) {
            return self::VISTAS;
        }

        return self::VISTAS + ['pedidos' => 'Pedidos', 'tipos' => 'Tipos que se pueden pedir'];
    }

    public function mount(): void
    {
        $alcance = $this->alcance();
        $this->exigir($alcance->rrhh);

        if (! array_key_exists($this->vista, self::vistas($alcance->confirmaPedidos))) {
            $this->vista = 'listado';
        }
    }

    public function updated(string $propiedad): void
    {
        $this->resetPage();
    }

    public function confirmarPedido(int $pedidoId, PedidosLicencia $pedidos): void
    {
        $this->procesar(fn () => $pedidos->confirmar($this->alcance(), $pedidoId), 'Pedido confirmado. Cargá la licencia en el sistema viejo.');
    }

    public function rechazarPedido(int $pedidoId, PedidosLicencia $pedidos): void
    {
        $this->procesar(fn () => $pedidos->rechazarRrhh($this->alcance(), $pedidoId, (string) ($this->motivos[$pedidoId] ?? '')), 'Pedido rechazado');
        unset($this->motivos[$pedidoId]);
    }

    public function guardarTipo(PedidosLicencia $pedidos): void
    {
        $this->procesar(function () use ($pedidos) {
            $pedidos->guardarTipo($this->alcance(), (int) $this->tipoPedibleId, $this->tipoRetroactivo, $this->tipoAdjunto);
            $this->reset(['tipoPedibleId', 'tipoRetroactivo', 'tipoAdjunto']);
        }, 'Tipo guardado');
    }

    public function quitarTipo(int $id, PedidosLicencia $pedidos): void
    {
        $this->procesar(fn () => $pedidos->quitarTipo($this->alcance(), $id), 'Tipo quitado');
    }

    private function procesar(callable $accion, string $exito): void
    {
        try {
            $this->consultar($accion);
            $this->toast('success', $exito);
        } catch (ValidationException $e) {
            $this->toast('error', (string) collect($e->errors())->flatten()->first());
        }
    }

    public function render(LicenciasRrhh $licencias, Organigrama $organigrama, PedidosLicencia $pedidos): View
    {
        $alcance = $this->alcance();

        $datos = $this->consultar(fn () => [
            'oficinas' => $organigrama->oficinas(),
            'tipos' => $licencias->tipos(),
        ] + match ($this->vista) {
            'adeudadas' => ['adeudadas' => $licencias->adeudadas($alcance, $this->oficinaId)],
            'pedidos' => ['porConfirmar' => $pedidos->porConfirmar($alcance), 'historial' => $pedidos->historial($alcance)],
            'tipos' => ['tiposPedibles' => $pedidos->tiposPedibles()],
            default => ['licencias' => $licencias->buscar($alcance, [
                'texto' => $this->texto,
                'tipo_id' => $this->tipoId,
                'oficina_id' => $this->oficinaId,
                'desde' => $this->desde ?: null,
                'hasta' => $this->hasta ?: null,
            ])],
        }, ['oficinas' => collect(), 'tipos' => collect()]);

        return view('livewire.rrhh.licencias', $datos + [
            'vistas' => self::vistas($alcance->confirmaPedidos),
            'pedidosPendientes' => PedidosLicencia::contadores(auth()->user())['confirmar'],
            'error' => $this->errorFuente,
        ]);
    }
}
