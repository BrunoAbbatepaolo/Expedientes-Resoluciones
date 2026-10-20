<?php

namespace App\Livewire;

use App\Livewire\Forms\ExpedienteForm;
use App\Models\Expediente;
use App\Models\Oficina;
use App\Models\Pase;
use App\Models\VistaExpedientes;
use App\Services\ExpedientesIpv;
use App\Traits\AuthorizesOficina;
use App\Traits\NotificaUi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Expedientes extends Component
{
    use AuthorizesOficina;
    use NotificaUi;
    use WithPagination;

    public $busquedaExp = '';

    public $expedienteEncontrado;

    public $asunto;

    public $causante;

    #[Locked]
    public $expedienteId;

    #[Locked]
    public $expedientePaseId;

    public $search = '';

    public $sinPermiso = false;

    public $sinOficina = false;

    public $oficinaUsuario;

    public ExpedienteForm $expedienteForm;

    public $filtro = [
        'fechaDesde' => null,
        'fechaHasta' => null,
    ];

    public $tipoVista = 'todos';

    #[Url(except: '')]
    public string $registrar = '';

    #[Locked]
    public bool $abrirNuevo = false;

    public $mostrarBoton = false;

    // Autocomplete oficinas
    public $query = '';

    public $oficinas = [];

    public function updatedQuery()
    {
        if (empty($this->query)) {
            $this->oficinas = [];
            $this->expedienteForm->ofi_salida = null;
            $this->expedienteForm->cod_area = null;
            $this->expedienteForm->cod_oficina = null;

            return;
        }

        // Si el query coincide exactamente con la oficina ya seleccionada, no buscar
        if ($this->expedienteForm->ofi_salida) {
            $oficinaActual = Oficina::find($this->expedienteForm->ofi_salida);
            if ($oficinaActual && $this->query === $oficinaActual->nombre) {
                $this->oficinas = [];

                return;
            }
        }

        $this->oficinas = Oficina::where('nombre', 'like', '%'.$this->query.'%')
            ->orWhere('codigo', 'like', '%'.$this->query.'%')
            ->take(10)
            ->get();
    }

    public function selectOficina($id)
    {
        $oficina = Oficina::find($id);
        if ($oficina) {
            $this->expedienteForm->fill([
                'ofi_salida' => $id,
                'cod_area' => $oficina->cod_area,
                'cod_oficina' => $oficina->codigo,
            ]);

            $this->query = $oficina->nombre;
            $this->oficinas = [];
        }
    }

    public function render(ExpedientesIpv $ipv)
    {
        $expedientes = $this->getExp();

        // Dónde está hoy cada expediente de la página según el sistema del IPV
        // (una sola consulta). Si mitiv no responde, la tabla se muestra igual.
        $ubicacionesIpv = $this->tipoVista === 'entrantes'
            ? collect()
            : rescue(fn () => $ipv->ubicaciones(collect($expedientes->items())->pluck('num_exp')->all()), collect());

        return view('livewire.Expedientes.expedientes', [
            'expedientes' => $expedientes,
            'ubicacionesIpv' => $ubicacionesIpv,
            'pasesInternos' => config('sirex.pases_internos'),
        ]);
    }

    public function mount()
    {
        $currentRoute = request()->route()?->getName() ?? 'expedientes';

        switch ($currentRoute) {
            case 'expedientes.ingresados':
                $this->tipoVista = 'ingresados';
                break;
            case 'expedientes.egresados':
                $this->tipoVista = 'egresados';
                break;
            case 'expedientes.entrantes':
                // La bandeja de pases internos solo existe si están activados.
                $this->tipoVista = config('sirex.pases_internos') ? 'entrantes' : 'todos';
                break;
            default:
                $this->tipoVista = 'todos';
        }
        $this->mostrarBoton = request()->routeIs('expedientes') || request()->routeIs('expedientes.ingresados');
        $oficinaId = auth()->user()?->oficinaAsignadaId()
            ?? auth()->user()?->oficinaIdPara('expediente_ver');

        if ($oficinaId) {
            $this->oficinaUsuario = auth()->user()->oficinaAsignadaId() === (int) $oficinaId
                ? auth()->user()->oficinaAsignada()
                : Oficina::find($oficinaId);
        }

        // Llegada desde la ficha IPV ("Registrar en mi oficina"): abre el alta ya buscado.
        if ($this->registrar !== '' && auth()->user()->permiso('expediente_editar')) {
            $this->busquedaExp = $this->registrar;
            $this->buscar();
            $this->abrirNuevo = true;
        }
    }

    public function getExp()
    {
        $query = Expediente::query();

        // 1) Permiso base
        if (! auth()->user()?->permiso('expediente_ver')) {
            $this->sinPermiso = true;

            return Expediente::whereRaw('1=0')->paginate(10);
        }

        // 2) Oficina desde 'oficina_asignada' (fallback a 'expediente_ver' si no existiera)
        $oficinaId = auth()->user()->oficinaAsignadaId()
            ?? auth()->user()->oficinaIdPara('expediente_ver');

        if (! $oficinaId) {
            $this->sinOficina = true;

            return Expediente::whereRaw('1=0')->paginate(10);
        }

        // Bandeja de entrada: expedientes con un pase pendiente hacia la oficina del usuario
        if ($this->tipoVista === 'entrantes') {
            return $this->getEntrantes((int) $oficinaId);
        }

        // 3) Filtrar por oficina del usuario + 4) tipo de vista.
        // 'egresados' es un caso especial: un expediente pasado a otra oficina deja
        // de tener oficina_id = la mía (ver Expedientes::aceptarPase()), así que no
        // puedo filtrarlo con deOficina() como las demás vistas o desaparecería de mi
        // listado apenas la oficina destino lo acepta. Lo resuelvo con un OR: sigue
        // contando como "egresado mío" si (a) lo tengo yo con fecha_salida cargada a
        // mano (cierre sin pase), o (b) hay un pase aceptado cuyo origen soy yo,
        // sin importar dónde esté ahora.
        if ($this->tipoVista === 'egresados') {
            $query->where(function ($q) use ($oficinaId) {
                $q->where(function ($q2) use ($oficinaId) {
                    $q2->deOficina($oficinaId)->whereNotNull('fecha_salida');
                })->orWhereHas('pases', function ($q2) use ($oficinaId) {
                    $q2->where('oficina_origen_id', $oficinaId)->where('estado', 'aceptado');
                });
            });
        } else {
            $query->deOficina((int) $oficinaId);

            if ($this->tipoVista === 'ingresados') {
                $query->whereNull('fecha_salida');
            }
        }

        // 5) Búsqueda libre
        if (! empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('num_exp', 'LIKE', "%{$search}%")
                    ->orWhere('asunto', 'LIKE', "%{$search}%")
                    ->orWhere('causante', 'LIKE', "%{$search}%")
                    ->orWhere('cod_area', 'LIKE', "%{$search}%")
                    ->orWhere('cod_oficina', 'LIKE', "%{$search}%");
            });
        }

        // 6) Filtros de fecha
        if (! empty($this->filtro['fechaDesde'])) {
            $fechaDesde = Carbon::parse($this->filtro['fechaDesde'])->startOfDay();
            $query->where('created_at', '>=', $fechaDesde);
        }

        if (! empty($this->filtro['fechaHasta'])) {
            $fechaHasta = Carbon::parse($this->filtro['fechaHasta'])->endOfDay();
            $query->where('created_at', '<=', $fechaHasta);
        }

        // 7) Orden y paginación
        return $query->orderByDesc('created_at')->paginate(10);
    }

    private function getEntrantes(int $oficinaId)
    {
        $query = Pase::where('oficina_destino_id', $oficinaId)
            ->where('estado', 'pendiente')
            ->with(['expediente', 'oficinaOrigen']);

        if (! empty($this->search)) {
            $search = $this->search;
            $query->whereHas('expediente', function ($q) use ($search) {
                $q->where('num_exp', 'LIKE', "%{$search}%")
                    ->orWhere('asunto', 'LIKE', "%{$search}%")
                    ->orWhere('causante', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderByDesc('fecha')->paginate(10);
    }

    public function aceptarPase($paseId)
    {
        abort_unless(config('sirex.pases_internos'), 404);

        $this->autorizarPermiso('expediente_editar');

        $oficinaId = auth()->user()?->oficinaAsignadaId()
            ?? auth()->user()?->oficinaIdPara('expediente_editar');

        DB::connection('mysql_admin')->transaction(function () use ($paseId, $oficinaId) {
            $pase = Pase::lockForUpdate()->findOrFail($paseId);

            abort_unless($oficinaId && (int) $pase->oficina_destino_id === (int) $oficinaId, 403);
            abort_unless($pase->estado === 'pendiente', 403);

            $pase->update(['estado' => 'aceptado']);
            // fecha_salida se limpia porque quedaba seteada desde el envío en la
            // oficina de origen: si no se resetea, el expediente entra a mi oficina
            // ya marcado como "egresado" (ver 'egresados' en Expedientes::getExp()).
            Expediente::where('id', $pase->expediente_id)->update([
                'oficina_id' => $pase->oficina_destino_id,
                'fecha_salida' => null,
            ]);
        });

        $this->toast('success', 'Expediente aceptado en tu oficina');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function buscar()
    {
        $this->busquedaExp = trim($this->busquedaExp);

        if (strlen($this->busquedaExp) > 0) {
            // Coincidencia exacta (sin distinguir mayúsculas): con '%...%' un número
            // parcial traía cualquier expediente que lo contuviera.
            try {
                $this->expedienteEncontrado = VistaExpedientes::where('numero', 'ILIKE', addcslashes($this->busquedaExp, '%_\\'))->first();
            } catch (\Throwable $e) {
                report($e);
                $this->expedienteEncontrado = null;
                $this->expedienteForm->reset();
                $this->toast('error', 'No se pudo consultar el sistema de expedientes. Intentá más tarde.', 3500);

                return;
            }

            if (! is_null($this->expedienteEncontrado)) { // Si el expediente se encuentra cargado en el 95 trae los datos necesarios.
                // Verificar si el expediente ya existe en la base de datos
                $expedienteExistente = Expediente::with(['oficina', 'oficinaById'])
                    ->where('num_exp', $this->expedienteEncontrado['numero'])
                    ->first();
                if ($expedienteExistente) { // Si el expediente ya existe, limpiar los valores y mostrar mensaje de error
                    $this->expedienteEncontrado = null;
                    $this->asunto = null;
                    $this->causante = null;
                    $this->expedienteForm->reset();
                    $oficinaName = $expedienteExistente->oficinaById->nombre
                        ?? $expedienteExistente->oficina->nombre
                        ?? 'Oficina no encontrada';
                    $this->toast('error', 'El expediente ya se encuentra cargado en la oficina: '.$oficinaName.'!');

                    return;
                }

                // Si no esta cargado, asignar valores
                $this->asunto = ! empty($this->expedienteEncontrado['asunto']) ? $this->expedienteEncontrado['asunto'] : $this->expedienteEncontrado['oficina'];
                $this->causante = $this->expedienteEncontrado['causante'];

                $this->expedienteForm->num_exp = $this->expedienteEncontrado['numero'];
                $this->expedienteForm->asunto = $this->asunto;
                if (! empty($this->expedienteEncontrado['folio'])) {
                    $this->expedienteForm->folio = $this->expedienteEncontrado['folio'];
                } else {
                    $this->expedienteForm->folio = 0;
                    $this->toast('error', 'El expediente no tiene numero de fojas!');
                }
                $this->expedienteForm->causante = $this->causante;
                $this->expedienteForm->fecha_ingreso = now()->format('Y-m-d');
            } else { // Si no se encuentra, limpiar los datos del imput.
                $this->expedienteEncontrado = null;
                $this->asunto = null;
                $this->causante = null;
                $this->expedienteForm->reset();
            }
        } else {
            // Si la búsqueda está vacía, limpiar los datos
            $this->expedienteEncontrado = null;
            $this->asunto = null;
            $this->causante = null;
            $this->expedienteForm->reset();
        }
    }

    public function guardar()
    {
        $this->autorizarPermiso('expediente_editar');

        if (! $this->expedienteEncontrado || blank($this->expedienteForm->num_exp)) {
            $this->toast('error', 'Buscá el expediente antes de guardarlo');

            return;
        }

        // Se chequea el número que efectivamente se va a guardar (el del form), no el
        // de la búsqueda: el form lo puede modificar el cliente.
        $expedienteExistente = Expediente::where('num_exp', $this->expedienteForm->num_exp)->first();

        if ($expedienteExistente) {
            $this->toast('error', 'El expediente ya se encuentra cargado');

            return;
        } else {
            // Obtener la oficina del usuario antes de guardar
            $oficinaId = auth()->user()->oficinaAsignadaId()
                ?? auth()->user()->oficinaIdPara('expediente_ver');

            if (! $oficinaId) {
                $this->toast('error', 'No tenés una oficina asignada. Pedile a un administrador que te asigne una antes de cargar expedientes.', 4000);

                return;
            }

            // Buscar la oficina para obtener los códigos
            $oficina = Oficina::find($oficinaId);

            if ($oficina) {
                // Asignar la oficina del usuario al formulario
                $this->expedienteForm->oficina_id = $oficinaId; // o como se llame el campo
            }

            $this->validate();
            $resultado = $this->expedienteForm->store();

            if (! $resultado) {
                $this->toast('error', 'No se pudo guardar el expediente. Intentá de nuevo.', 3500);

                return;
            }

            $this->toast('success', 'El expediente se cargo correctamente!');
        }

        $this->cerrarModal('modal-exp');
        $this->expedienteForm->num_exp = null;
        $this->expedienteForm->asunto = null;
        $this->expedienteForm->folio = null;
        $this->expedienteForm->causante = null;
        $this->expedienteForm->fecha_ingreso = null;
        $this->expedienteEncontrado = null;
    }

    public function editar($id)
    {
        $expediente = Expediente::findOrFail($id);
        $this->autorizarExpediente($expediente, 'expediente_editar');

        $this->expedienteForm->loadExpMitiv($expediente);

        // Fechas
        $this->expedienteForm->fecha_ingreso = Carbon::parse($expediente->fecha_ingreso)->format('Y-m-d');
        $this->expedienteForm->fecha_salida = $expediente->fecha_salida
            ? Carbon::parse($expediente->fecha_salida)->format('Y-m-d')
            : null;
    }

    public function actualizar()
    {
        // Sin expediente cargado (no se pasó por editar()) no hay nada que actualizar.
        if (! isset($this->expedienteForm->expediente)) {
            return;
        }

        $this->autorizarExpediente($this->expedienteForm->expediente, 'expediente_editar');

        // update(): 1 = guardado, -1 = sin cambios, 0 = error (ya logueado en el form).
        $resultado = $this->expedienteForm->update();

        if ($resultado === 0) {
            $this->toast('error', 'El Expediente no se pudo Editar');

            return;
        }

        $this->cerrarModal('modal-editarExpediente');

        if ($resultado === -1) {
            $this->toast('info', 'No hay cambios para guardar');

            return;
        }

        $this->toast('success', 'El Expediente se editó Correctamente');
    }

    /**
     * Abre el modal para iniciar un pase (traspaso a otra oficina), separado de
     * "Editar" para no volver a mezclar datos del expediente con el traslado.
     */
    public function abrirPase($id)
    {
        abort_unless(config('sirex.pases_internos'), 404);

        $expediente = Expediente::findOrFail($id);
        $this->autorizarExpediente($expediente, 'expediente_editar');

        $this->expedientePaseId = $expediente->id;
        $this->query = '';
        $this->oficinas = [];
        $this->expedienteForm->ofi_salida = null;
        $this->expedienteForm->cod_area = null;
        $this->expedienteForm->cod_oficina = null;
    }

    public function confirmarPase()
    {
        abort_unless(config('sirex.pases_internos'), 404);

        if (! $this->expedienteForm->ofi_salida) {
            $this->toast('error', 'Elegí una oficina de destino');

            return;
        }

        $expediente = Expediente::findOrFail($this->expedientePaseId);
        $this->autorizarExpediente($expediente, 'expediente_editar');

        if (Pase::where('expediente_id', $expediente->id)->where('estado', 'pendiente')->exists()) {
            $this->toast('error', 'Este expediente ya tiene un pase pendiente de aceptación');

            return;
        }

        $oficina = Oficina::findOrFail($this->expedienteForm->ofi_salida);

        DB::connection('mysql_admin')->transaction(function () use ($expediente, $oficina) {
            $expediente->update([
                'ofi_salida' => $oficina->id,
                'cod_area' => $oficina->cod_area,
                'cod_oficina' => $oficina->codigo,
            ]);

            Pase::create([
                'expediente_id' => $expediente->id,
                'oficina_id' => $oficina->id,
                'oficina_origen_id' => $expediente->oficina_id,
                'oficina_destino_id' => $oficina->id,
                'fecha' => now()->toDateString(),
                'user_id' => auth()->id(),
                'importado' => false,
                'firmado' => false,
                'estado' => 'pendiente',
            ]);
        });

        $this->cancelarPase();

        $this->toast('success', 'Pase iniciado, pendiente de aceptación en '.$oficina->nombre);
    }

    public function cancelarPase()
    {
        $this->cerrarModal('modal-realizarPase');
        $this->expedientePaseId = null;
        $this->query = '';
        $this->oficinas = [];
    }

    public function confirmarBorrado($expedienteId)
    {
        $this->expedienteId = $expedienteId;
    }

    public function eliminarExpediente()
    {
        $expediente = Expediente::findOrFail($this->expedienteId);
        $this->autorizarExpediente($expediente, 'expediente_editar');

        $expediente->delete();
        $this->cerrarModal('modal-ConfirmarBorrado');
        $this->reset('expedienteId');
        $this->toast('success', 'Expediente eliminado');
    }

    public static function obtenerDMY($fecha = null)
    {
        if (is_null($fecha)) {
            return '-';
        } else {
            $fecha = Carbon::parse($fecha)->locale('es');

            return $fecha->format('d-m-Y');
        }
    }

    public function cerrar()
    {
        $this->cerrarModal('modal-exp');
        $this->expedienteForm->num_exp = null;
        $this->expedienteForm->asunto = null;
        $this->expedienteForm->folio = null;
        $this->expedienteForm->causante = null;
        $this->expedienteForm->fecha_ingreso = null;
        $this->expedienteEncontrado = null;
    }

    public function cancelarModal()
    {
        $this->cerrarModal('modal-editarExpediente');
    }

    public function aplicarFiltros()
    {
        $this->validate([
            'filtro.fechaDesde' => 'nullable|date',
            'filtro.fechaHasta' => 'nullable|date',
        ]);
        $this->cerrarModal('modal-filtro');
        $this->toast('success', 'Filtro Aplicado!');
    }

    public function limpiarFiltros()
    {
        $this->filtro['fechaDesde'] = null;
        $this->filtro['fechaHasta'] = null;
        $this->cerrarModal('modal-filtro');
    }

    public function verDetalle($id)
    {
        $this->redirectRoute('expedientes.detalle', ['id' => $id]);
    }

    public function formatearCausante($causante)
    {
        return str_replace('DEPARTAMENTO ', 'DEPTO. ', $causante);
    }

    public function limpiarBusqueda()
    {
        $this->busquedaExp = '';
    }
}
