<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Forms\ExpedienteForm;
use Carbon\Carbon;
use App\Models\Oficina;
use App\Models\Expediente;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use App\Models\User;

class Expedientes extends Component
{
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
            return;
        }

        $this->oficinas = Oficina::where('nombre', 'like', '%' . $this->query . '%')
            ->orWhere('codigo', 'like', '%' . $this->query . '%')
            ->take(10)
            ->get();
    }

    public function selectOficina($id)
    {
        $oficina = Oficina::find($id);
        if ($oficina) {
            $this->expedienteForm->fill([
                'ofi_salida'   => $id,
                'cod_area'     => $oficina->cod_area,
                'cod_oficina'  => $oficina->codigo,
            ]);

            $this->query = $oficina->nombre;
            $this->oficinas = [];
        }
    }

    public function render()
    {
        $expedientes = $this->getExp();
        return view('livewire.expedientes.expedientes', [
            'expedientes' => $expedientes
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
            default:
                $this->tipoVista = 'todos';
        }
        $this->mostrarBoton = request()->routeIs('expedientes') || request()->routeIs('expedientes.ingresados');
        $oficinaId = auth()->user()->oficinaAsignadaId()
            ?? auth()->user()->oficinaIdPara('expediente_ver');

        if ($oficinaId) {
            $this->oficinaUsuario = Oficina::find($oficinaId);
        }
    }

    public function getExp()
    {
        $query = Expediente::query();

        // 1) Permiso base
        if (!auth()->user()->permiso('expediente_ver')) {
            $this->sinPermiso = true;
            return Expediente::whereRaw('1=0')->paginate(10);
        }

        // 2) Oficina desde 'oficina_asignada' (fallback a 'expediente_ver' si no existiera)
        $oficinaId = auth()->user()->oficinaAsignadaId()
            ?? auth()->user()->oficinaIdPara('expediente_ver');

        if (!$oficinaId) {
            $this->sinOficina = true;
            return Expediente::whereRaw('1=0')->paginate(10);
        }

        // 3) Filtrar por oficina del usuario
        $query->deOficina((int) $oficinaId);

        // 4) Tipo de vista
        switch ($this->tipoVista) {
            case 'ingresados':
                $query->whereNull('fecha_salida');
                break;
            case 'egresados':
                $query->whereNotNull('fecha_salida');
                break;
        }

        // 5) Búsqueda libre
        if (!empty($this->search)) {
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
        if (!empty($this->filtro['fechaDesde'])) {
            $fechaDesde = \Carbon\Carbon::parse($this->filtro['fechaDesde'])->startOfDay();
            $query->where('created_at', '>=', $fechaDesde);
        }

        if (!empty($this->filtro['fechaHasta'])) {
            $fechaHasta = \Carbon\Carbon::parse($this->filtro['fechaHasta'])->endOfDay();
            $query->where('created_at', '<=', $fechaHasta);
        }

        // 7) Orden y paginación
        return $query->orderByDesc('created_at')->paginate(10);
    }


    public function updatedSearch()
    {
        $this->resetPage();
    }


    public function buscar()
    {
        if (strlen($this->busquedaExp) > 0) {
            $this->expedienteEncontrado = \App\Models\VistaExpedientes::where('numero', 'ILIKE', '%' . $this->busquedaExp . '%')->first();

            if (!is_null($this->expedienteEncontrado)) { //Si el expediente se encuentra cargado en el 95 trae los datos necesarios.
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
                    LivewireAlert::title('El expediente ya se encuentra cargado en la oficina: ' . $oficinaName . '!')
                        ->error()
                        ->timer(2500)
                        ->toast()
                        ->position('top-end')
                        ->show();
                    return;
                }

                // Si no esta cargado, asignar valores
                $this->asunto = !empty($this->expedienteEncontrado['asunto']) ? $this->expedienteEncontrado['asunto'] : $this->expedienteEncontrado['oficina'];
                $this->causante = $this->expedienteEncontrado['causante'];

                $this->expedienteForm->num_exp = $this->expedienteEncontrado['numero'];
                $this->expedienteForm->asunto = $this->asunto;
                if (!empty($this->expedienteEncontrado['folio'])) {
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
        $this->expedienteForm->fecha_ingreso = now()->format('d-m-Y'); //Establece la fecha actual
    }


    public function guardar()
    {
        $expedienteExistente = \App\Models\Expediente::where('num_exp', $this->expedienteEncontrado['numero'])->first();

        if ($expedienteExistente) {
            // El expediente ya existe - no hacer nada
        } else {
            // Obtener la oficina del usuario antes de guardar
            $oficinaId = auth()->user()->oficinaAsignadaId()
                ?? auth()->user()->oficinaIdPara('expediente_ver');

            if ($oficinaId) {
                // Buscar la oficina para obtener los códigos
                $oficina = Oficina::find($oficinaId);

                if ($oficina) {
                    // Asignar la oficina del usuario al formulario
                    $this->expedienteForm->oficina_id = $oficinaId; // o como se llame el campo
                }
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
        $expediente = \App\Models\Expediente::find($id);

        $this->expedienteForm->loadExpMitiv($expediente);

        // Fechas
        $this->expedienteForm->fecha_ingreso = Carbon::parse($expediente->fecha_ingreso)->format('Y-m-d');
        $this->expedienteForm->fecha_salida = $expediente->fecha_salida
            ? Carbon::parse($expediente->fecha_salida)->format('Y-m-d')
            : null;

        // Oficina
        $this->expedienteForm->ofi_salida = $expediente->ofi_salida ?? null;

        // Limpiar el campo de búsqueda visible
        $this->query = '';
    }




    public function actualizar()
    {
        try {
            // Si hay una oficina seleccionada, actualiza los campos relacionados
            if ($this->expedienteForm->ofi_salida) {
                $oficina = Oficina::on('mysql_legui')
                    ->find($this->expedienteForm->ofi_salida);

                if ($oficina) {
                    $this->expedienteForm->cod_area = $oficina->cod_area;
                    $this->expedienteForm->cod_oficina = $oficina->codigo;
                } else {
                    // Manejar el caso donde el ID de oficina no existe
                    $this->expedienteForm->cod_area = null;
                    $this->expedienteForm->cod_oficina = null;
                }
            } else {
                // Si no hay oficina seleccionada, establecer valores nulos
                $this->expedienteForm->cod_area = null;
                $this->expedienteForm->cod_oficina = null;
            }

            // Llamar al método update del formulario
            $resultado = $this->expedienteForm->update();
            $this->modal('modal-editarExpediente')->close();
            LivewireAlert::title('El Expediente se editó Correctamente')->success()->timer(2500)->toast()->position('top-end')->show();
            // Resto de tu código...
        } catch (\Exception $e) {
            // Manejar excepciones
            LivewireAlert::title('El Expediente no se pudo Editar')->error()->timer(2500)->toast()->position('top-end')->show();
        }
    }



    public function confirmarBorrado($expedienteId)
    {
        $this->expedienteId = $expedienteId;
    }

    public function eliminarExpediente()
    {
        \App\Models\Expediente::findOrFail($this->expedienteId)->delete();
        $this->modal('modal-ConfirmarBorrado')->close();
        $this->reset('expedienteId');
        LivewireAlert::title('Expediente eliminado')->success()->timer(2500)->toast()->position('top-end')->show();
    }

    public static function obtenerDMY($fecha = null)
    {
        if (is_null($fecha)) {
            return "-";
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
