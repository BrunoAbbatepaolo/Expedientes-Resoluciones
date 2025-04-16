<?php

namespace App\Livewire;

use Livewire\Component;
use App\Livewire\Flux;
use Livewire\WithPagination;
use App\Livewire\Forms\ExpedienteForm;
use \Livewire\Attributes\On;
use Carbon\Carbon;
use App\Models\Oficina;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

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

    public ExpedienteForm $expedienteForm;

    public $menuVisible = null; // Para controlar el menú desplegable

    public $filtro = [
        'fechaDesde' => null,
        'fechaHasta' => null,
    ];

    public $tipoVista = 'todos';
    public $mostrarBoton = false;

    // Para el autocompletar de oficinas
    public $query = ''; // Texto de búsqueda
    public $oficinas = []; // Resultados de la búsqueda
    // Métodos para la funcionalidad de oficina de salida
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
                'ofi_salida' => $id, // Aquí almacenamos la ID de la oficina
                'cod_area' => $oficina->cod_area,
                'cod_oficina' => $oficina->codigo,
            ]);

            $this->query = $oficina->nombre; // Mostrar el nombre en el input
            $this->oficinas = []; // Limpiar resultados del autocompletado
        }
    }

    public function render()
    {
        $expedientes = $this->getExp();
        return view('livewire.expedientes', [
            'expedientes' => $expedientes
        ]);
    }


    public function mount()
    {
        // Detectar la ruta actual y establecer el tipo de vista
        $currentRoute = request()->route()->getName();

        switch ($currentRoute) {
            case 'expediente.ingresados':
                $this->tipoVista = 'ingresados';
                break;
            case 'expediente.egresados':
                $this->tipoVista = 'egresados';
                break;
            default:
                $this->tipoVista = 'todos';
        }
        $this->mostrarBoton = request()->routeIs('expediente') || request()->routeIs('expediente.ingresados');
    }

    public function getExp()
    {
        $query = \App\Models\Expediente::query();

        // Filtro por tipo de vista
        switch ($this->tipoVista) {
            case 'ingresados':
                $query->whereNull('fecha_salida');
                break;
            case 'egresados':
                $query->whereNotNull('fecha_salida');
                break;
        }

        // Tus filtros existentes
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('num_exp', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('asunto', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('causante', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('cod_area', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('cod_oficina', 'LIKE', '%' . $this->search . '%');
            });
        }

        if (!empty($this->filtro['fechaDesde'])) {
            $fechaDesde = Carbon::parse($this->filtro['fechaDesde'])->startOfDay();
            $query->where('created_at', '>=', $fechaDesde);
        }

        if (!empty($this->filtro['fechaHasta'])) {
            $fechaHasta = Carbon::parse($this->filtro['fechaHasta'])->endOfDay();
            $query->where('created_at', '<=', $fechaHasta);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }


    public function buscar()
    {
        if (strlen($this->busquedaExp) > 0) {
            $this->expedienteEncontrado = \App\Models\VistaExpedientes::where('numero', 'ILIKE', '%' . $this->busquedaExp . '%')->first();

            if (!is_null($this->expedienteEncontrado)) {
                // Verificar si el expediente ya existe en la base de datos
                $expedienteExistente = \App\Models\Expediente::where('num_exp', $this->expedienteEncontrado['numero'])->first();

                if ($expedienteExistente) {
                    // Si el expediente ya existe, limpiar los valores y mostrar mensaje de error
                    $this->expedienteEncontrado = null;
                    $this->asunto = null;
                    $this->causante = null;
                    $this->expedienteForm->reset();
                    return;
                }

                // Si no existe, asignar valores
                $this->asunto = !empty($this->expedienteEncontrado['asunto']) ? $this->expedienteEncontrado['asunto'] : $this->expedienteEncontrado['oficina'];
                $this->causante = $this->expedienteEncontrado['causante'];

                $this->expedienteForm->num_exp = $this->expedienteEncontrado['numero'];
                $this->expedienteForm->asunto = $this->asunto;
                $this->expedienteForm->folio = $this->expedienteEncontrado['folio'];
                $this->expedienteForm->causante = $this->causante;
                $this->expedienteForm->fecha_ingreso = now()->format('Y-m-d');
            } else {
                // Si no se encuentra, limpiar los datos
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
        } else {
            $this->validate();
            $resultado = $this->expedienteForm->store();
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
        //Codigo para que la fecha aparezca como placeholder
        $this->expedienteForm->fecha_ingreso = Carbon::parse($expediente->fecha_ingreso)->format('Y-m-d'); // Esto está de más
        $this->expedienteForm->fecha_salida = $expediente->fecha_salida
            ? Carbon::parse($expediente->fecha_salida)->format('Y-m-d')
            : null;
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
                //'width' => '200px',
                // 'height' => '20px',
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
}
