<?php

namespace App\Livewire;

use App\Livewire\Forms\ExpedienteForm;
use App\Models\Expediente;
use App\Models\Oficina;
use App\Models\Pase;
use App\Traits\AuthorizesOficina;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class Expedientes extends Component
{
    use AuthorizesOficina;
    use WithPagination;

    public $modalExp = false;

    public $modalEdit = false;

    public $busquedaExp = '';

    public $expedienteEncontrado;

    public $expedienteExistente;

    public $selectedExpediente;

    public $asunto;

    public $causante;

    public $expedienteId;

    public $expedientePaseId;

    public $modalFiltro;

    public $search = '';

    public $sinPermiso = false;

    public $sinOficina = false;

    public $oficinaUsuario;

    public ExpedienteForm $expedienteForm;

    public $menuVisible = null;

    public $filtro = [
        'fechaDesde' => null,
        'fechaHasta' => null,
    ];

    public $tipoVista = 'todos';

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

    public function render()
    {
        $expedientes = $this->getExp();

        return view('livewire.Expedientes.expedientes', [
            'expedientes' => $expedientes,
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
                $this->tipoVista = 'entrantes';
                break;
            default:
                $this->tipoVista = 'todos';
        }
        $this->mostrarBoton = request()->routeIs('expedientes') || request()->routeIs('expedientes.ingresados');
        $oficinaId = auth()->user()?->oficinaAsignadaId()
            ?? auth()->user()?->oficinaIdPara('expediente_ver');

        if ($oficinaId) {
            $this->oficinaUsuario = Oficina::find($oficinaId);
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
        $oficinaId = auth()->user()?->oficinaAsignadaId()
            ?? auth()->user()?->oficinaIdPara('expediente_ver');

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
            $fechaDesde = \Carbon\Carbon::parse($this->filtro['fechaDesde'])->startOfDay();
            $query->where('created_at', '>=', $fechaDesde);
        }

        if (! empty($this->filtro['fechaHasta'])) {
            $fechaHasta = \Carbon\Carbon::parse($this->filtro['fechaHasta'])->endOfDay();
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

        LivewireAlert::title('Expediente aceptado en tu oficina')
            ->success()
            ->timer(2500)
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function buscar()
    {
        if (strlen($this->busquedaExp) > 0) {
            $this->expedienteEncontrado = \App\Models\VistaExpedientes::where('numero', 'ILIKE', '%'.$this->busquedaExp.'%')->first();

            if (! is_null($this->expedienteEncontrado)) { // Si el expediente se encuentra cargado en el 95 trae los datos necesarios.
                // Verificar si el expediente ya existe en la base de datos
                $expedienteExistente = \App\Models\Expediente::with(['oficina', 'oficinaById'])
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
                    LivewireAlert::title('El expediente ya se encuentra cargado en la oficina: '.$oficinaName.'!')
                        ->error()
                        ->timer(2500)
                        ->toast()
                        ->position('top-end')
                        ->show();

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
                    LivewireAlert::title('El expediente no tiene numero de fojas!')
                        ->error()
                        ->timer(2500)
                        ->toast()
                        ->position('top-end')
                        ->show();
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

    public function seleccionar()
    {
        $this->expedienteForm->num_exp = $this->expedienteEncontrado['numero'];
        $this->expedienteForm->asunto = $this->asunto;
        $this->expedienteForm->folio = $this->expedienteEncontrado['folio'];
        $this->expedienteForm->causante = $this->causante;
        $this->expedienteForm->fecha_ingreso = now()->format('d-m-Y'); // Establece la fecha actual
    }

    public function guardar()
    {
        $expedienteExistente = \App\Models\Expediente::where('num_exp', $this->expedienteEncontrado['numero'])->first();

        if ($expedienteExistente) {

        } else {
            // Obtener la oficina del usuario antes de guardar
            $oficinaId = auth()->user()->oficinaAsignadaId()
                ?? auth()->user()->oficinaIdPara('expediente_ver');

            if (! $oficinaId) {
                LivewireAlert::title('No tenés una oficina asignada')
                    ->text('Pedile a un administrador que te asigne una oficina antes de cargar expedientes.')
                    ->error()
                    ->timer(4000)
                    ->toast()
                    ->position('top-end')
                    ->show();

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
            LivewireAlert::title('El expediente se cargo correctamente!')
                ->success()
                ->timer(2500)
                ->toast()
                ->position('top-end')
                ->show();
        }

        $this->modal('modal-exp')->close();
        $this->expedienteForm->num_exp = null;
        $this->expedienteForm->asunto = null;
        $this->expedienteForm->folio = null;
        $this->expedienteForm->causante = null;
        $this->expedienteForm->fecha_ingreso = null;
        $this->expedienteEncontrado = null;
    }

    public function editar($id)
    {
        $expediente = \App\Models\Expediente::findOrFail($id);
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
        if ($this->expedienteForm->expediente) {
            $this->autorizarExpediente($this->expedienteForm->expediente, 'expediente_editar');
        }

        try {
            $resultado = $this->expedienteForm->update();
            $this->modal('modal-editarExpediente')->close();
            LivewireAlert::title('El Expediente se editó Correctamente')->success()->timer(2500)->toast()->position('top-end')->show();
        } catch (\Exception $e) {
            LivewireAlert::title('El Expediente no se pudo Editar')->error()->timer(2500)->toast()->position('top-end')->show();
        }
    }

    /**
     * Abre el modal para iniciar un pase (traspaso a otra oficina), separado de
     * "Editar" para no volver a mezclar datos del expediente con el traslado.
     */
    public function abrirPase($id)
    {
        $expediente = \App\Models\Expediente::findOrFail($id);
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
        if (! $this->expedienteForm->ofi_salida) {
            LivewireAlert::title('Elegí una oficina de destino')->error()->timer(2500)->toast()->position('top-end')->show();

            return;
        }

        $expediente = \App\Models\Expediente::findOrFail($this->expedientePaseId);
        $this->autorizarExpediente($expediente, 'expediente_editar');

        if (Pase::where('expediente_id', $expediente->id)->where('estado', 'pendiente')->exists()) {
            LivewireAlert::title('Este expediente ya tiene un pase pendiente de aceptación')->error()->timer(2500)->toast()->position('top-end')->show();

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

        LivewireAlert::title('Pase iniciado, pendiente de aceptación en '.$oficina->nombre)
            ->success()
            ->timer(2500)
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function cancelarPase()
    {
        $this->modal('modal-realizarPase')->close();
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
        $expediente = \App\Models\Expediente::findOrFail($this->expedienteId);
        $this->autorizarExpediente($expediente, 'expediente_editar');

        $expediente->delete();
        $this->modal('modal-ConfirmarBorrado')->close();
        $this->reset('expedienteId');
        LivewireAlert::title('Expediente eliminado')->success()->timer(2500)->toast()->position('top-end')->show();
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
        $this->modal('modal-exp')->close();
        $this->expedienteForm->num_exp = null;
        $this->expedienteForm->asunto = null;
        $this->expedienteForm->folio = null;
        $this->expedienteForm->causante = null;
        $this->expedienteForm->fecha_ingreso = null;
        $this->expedienteEncontrado = null;
    }

    public function cancelarModal()
    {
        $this->modal('modal-editarExpediente')->close();
    }

    public function toggleMenu($id)
    {
        if ($this->menuVisible === $id) {
            $this->menuVisible = null; // Cerrar el menú si ya está abierto
        } else {
            $this->menuVisible = $id; // Mostrar el menú del expediente actual
        }
    }

    public function aplicarFiltros()
    {
        $this->validate([
            'filtro.fechaDesde' => 'nullable|date',
            'filtro.fechaHasta' => 'nullable|date',
        ]);
        // Aplicar filtros en el método getExp()
        $this->render();
        $this->modal('modal-filtro')->close();
        LivewireAlert::title('Filtro Aplicado!')
            ->success()
            ->position('top-end')
            ->timer(2500)
            ->toast()
            ->withOptions([
                'background' => '#f0f0f0',
                'customClass' => [
                    'popup' => 'animate_animated animate_bounceIn',
                    'title' => 'scale-85', // texto más chico
                    'icon' => 'scale-85', // ícono más chico (Tailwind),
                ],
                'allowOutsideClick' => true,
            ])
            ->show();
    }

    public function limpiarFiltros()
    {
        $this->filtro['fechaDesde'] = null;
        $this->filtro['fechaHasta'] = null;
        $this->render();
        $this->modal('modal-filtro')->close();
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
