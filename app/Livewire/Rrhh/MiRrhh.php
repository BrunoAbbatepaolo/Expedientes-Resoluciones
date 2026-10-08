<?php

namespace App\Livewire\Rrhh;

use App\Enums\EstadoDia;
use App\Enums\EstadoPedido;
use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Livewire\Rrhh\Concerns\NavegaMeses;
use App\Services\Rrhh\Contracts\Asistencias;
use App\Services\Rrhh\Contracts\Calendario;
use App\Services\Rrhh\Contracts\Legajos;
use App\Services\Rrhh\Contracts\Licencias;
use App\Services\Rrhh\Hora;
use App\Services\Rrhh\PedidosLicencia;
use App\Traits\NotificaUi;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Lo propio de cada empleado: saldo de vacaciones, fichadas del mes, licencias, faltas
 * y sus datos. Se identifica por el legajo del usuario. Si los pedidos están habilitados,
 * también pide licencias y sigue el estado de sus pedidos.
 */
#[Title('Mi RRHH')]
class MiRrhh extends Component
{
    use ConAlcance;
    use NavegaMeses;
    use NotificaUi;
    use WithFileUploads;

    /** Formulario "Pedir licencia". */
    public array $pedido = ['tipo_ausencia_id' => '', 'desde' => '', 'hasta' => '', 'observacion' => ''];

    public ?TemporaryUploadedFile $adjunto = null;

    public bool $pidiendo = false;

    public function pedirLicencia(PedidosLicencia $pedidos): void
    {
        $datos = array_map(fn ($v) => $v === '' ? null : $v, $this->pedido);

        try {
            $creado = $pedidos->crear($this->alcance(), $datos, $this->adjunto);
        } catch (ValidationException $e) {
            $this->setErrorBag(collect($e->errors())->mapWithKeys(fn ($m, $k) => [$k === 'adjunto' ? 'adjunto' : 'pedido.'.$k => $m])->all());

            return;
        }

        $this->reset(['pedido', 'adjunto', 'pidiendo']);
        $this->resetErrorBag();
        $this->toast('success', $creado->estado === EstadoPedido::AprobadoJefe ? 'Pedido enviado: no tenés jefe asignado, pasa directo a RRHH' : 'Pedido enviado a tu jefe');
    }

    public function cancelarPedido(int $pedidoId, PedidosLicencia $pedidos): void
    {
        try {
            $pedidos->cancelar($this->alcance(), $pedidoId);
            $this->toast('success', 'Pedido cancelado');
        } catch (ValidationException $e) {
            $this->toast('error', collect($e->errors())->flatten()->first());
        }
    }

    public function render(Legajos $legajos, Asistencias $asistencias, Licencias $licencias, Calendario $calendario, PedidosLicencia $pedidos): View
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
        $conPedidos = PedidosLicencia::habilitado() && $alcance->empleadoId !== null;
        $vista = $conPedidos && ! $this->errorFuente
            ? $this->consultar(fn () => $pedidos->previsualizar(
                $alcance,
                $this->pedido['tipo_ausencia_id'] !== '' ? (int) $this->pedido['tipo_ausencia_id'] : null,
                $this->pedido['desde'] ?: null,
                $this->pedido['hasta'] ?: null,
            ))
            : null;

        return view('livewire.rrhh.mi-rrhh', $datos + [
            'dias' => $dias->all(),
            'faltas' => $dias->where('estado', EstadoDia::Falta)->count(),
            'tardes' => $dias->where('estado', EstadoDia::Tarde)->count(),
            'conPedidos' => $conPedidos,
            'misPedidos' => $conPedidos ? $pedidos->mios($alcance) : collect(),
            'tiposPedibles' => $conPedidos ? $pedidos->tiposPedibles() : collect(),
            'vistaPedido' => $vista,
            'hoyIso' => $hoy->toDateString(),
            'error' => $this->errorFuente,
        ]);
    }
}
