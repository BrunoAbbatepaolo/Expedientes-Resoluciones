<?php

namespace App\Livewire;

use App\Livewire\Forms\ResolucionForm;
use App\Models\Resolucion;
use App\Traits\AuthorizesOficina;
use App\Traits\NotificaUi;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Resoluciones extends Component
{
    use AuthorizesOficina;
    use NotificaUi;
    use WithFileUploads;
    use WithPagination;

    private const POR_PAGINA = 20;

    public ResolucionForm $resolucionForm;

    /** Búsqueda por número de expediente, de resolución o barrio/casa. */
    #[Url(as: 'q', except: '')]
    public string $buscar = '';

    /** Filtro por tipo (CrearResolucion::TIPOS) o 'otras' (las cargadas sin tipo). */
    #[Url(except: '')]
    public string $tipo = '';

    /** Resolución cuyo historial se está mostrando. */
    #[Locked]
    public ?int $historialId = null;

    // Propiedad para saber qué registro eliminar
    #[Locked]
    public $resolucionIdParaBorrar;

    /**
     * Prepara el modal de Edición cargando los datos
     */
    public function cargarResolucion($id)
    {
        $this->resetValidation();
        $resolucion = Resolucion::findOrFail($id);
        $this->autorizarResolucion($resolucion, 'resolucion_editar');

        // Pasamos la data al Form Object
        $this->resolucionForm->loadResolucion($resolucion);
    }

    /**
     * Guarda los cambios de la EDICIÓN
     */
    public function guardarEdicion()
    {
        $this->autorizarResolucion($this->resolucionForm->resolucion, 'resolucion_editar');

        // Ejecuta el método update de tu Form Object
        $this->resolucionForm->update();

        // Cerramos el modal de edición
        $this->cerrarModal('edit-profile');
        $this->toast('success', 'Resolución modificada con éxito');
    }

    /**
     * Almacena temporalmente el ID de la resolución que se quiere borrar
     */
    public function confirmarBorrado($id)
    {
        $this->resolucionIdParaBorrar = $id;
    }

    /**
     * Ejecuta la eliminación definitiva
     */
    public function borrar()
    {
        if ($this->resolucionIdParaBorrar) {
            $resolucion = Resolucion::findOrFail($this->resolucionIdParaBorrar);
            $this->autorizarResolucion($resolucion, 'resolucion_editar');

            $resolucion->delete();
            $this->resolucionIdParaBorrar = null;

            $this->cerrarModal('delete-profile');
            $this->toast('success', 'Resolución eliminada');
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset('buscar', 'tipo');
        $this->resetPage();
    }

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatingTipo(): void
    {
        $this->resetPage();
    }

    /**
     * Quién creó y modificó la resolución (tabla de auditoría), del más nuevo al más viejo.
     *
     * @return list<array{cuando: string, quien: string, que: string, cambios: list<string>}>
     */
    #[Computed]
    public function historial(): array
    {
        if (! $this->historialId) {
            return [];
        }

        $resolucion = Resolucion::findOrFail($this->historialId);
        $this->autorizarResolucion($resolucion, 'resolucion_ver');

        $etiquetas = [
            'numero_exp' => 'Expediente', 'numero_resolucion' => 'N° de resolución', 'fecha' => 'Fecha',
            'cod_barrio' => 'Barrio', 'cod_casa' => 'Casa', 'fecha_ingreso' => 'Fecha de ingreso', 'tipo' => 'Tipo',
        ];

        return $resolucion->audits()->with('user')->latest('id')->limit(50)->get()->map(function ($a) use ($etiquetas) {
            $cambios = [];
            foreach ((array) $a->new_values as $campo => $nuevo) {
                if (! isset($etiquetas[$campo]) || $a->event === 'created') {
                    continue;
                }
                $antes = $a->old_values[$campo] ?? null;
                $cambios[] = $etiquetas[$campo].': '.($antes !== null && $antes !== '' ? $antes : '(vacío)').' → '.($nuevo !== null && $nuevo !== '' ? $nuevo : '(vacío)');
            }

            return [
                'cuando' => $a->created_at?->format('d/m/Y H:i') ?? '',
                'quien' => $a->user->name ?? 'Sistema',
                'que' => match ($a->event) {
                    'created' => 'Creó la resolución',
                    'deleted' => 'Eliminó la resolución',
                    default => 'Modificó la resolución',
                },
                'cambios' => $cambios,
            ];
        })->all();
    }

    public function verHistorial(int $id): void
    {
        $resolucion = Resolucion::findOrFail($id);
        $this->autorizarResolucion($resolucion, 'resolucion_ver');
        $this->historialId = $id;
        unset($this->historial);
    }

    public function render()
    {
        // Sin permiso no se consulta nada (la vista ya muestra el aviso).
        if (! auth()->user()?->permiso('resolucion_ver')) {
            return view('livewire.resoluciones', [
                'resolucionesConExpediente' => new LengthAwarePaginator([], 0, self::POR_PAGINA),
                'tiposPresentes' => collect(),
                'total' => 0,
            ]);
        }

        $visibles = Resolucion::query()->visiblesPara(auth()->user());

        // Tipos presentes (para los filtros) y total, antes de aplicar la búsqueda.
        $tiposPresentes = (clone $visibles)->select('tipo')->distinct()->pluck('tipo')
            ->map(fn ($t) => $t ?: 'otras')->unique()->sort()->values();
        $total = (clone $visibles)->count();

        $tipo = $this->tipo === 'otras' || in_array($this->tipo, CrearResolucion::TIPOS, true) ? $this->tipo : '';
        $resoluciones = $visibles->filtrar($this->buscar, $tipo)->with('archivos')->latest()->paginate(self::POR_PAGINA);

        // 'expediente' viene de pgsql_mitiv (otro servidor): si no responde, se
        // muestra el listado igual, sin el número de expediente.
        try {
            $resoluciones->getCollection()->load('expediente');
        } catch (\Throwable $e) {
            report($e);
            $resoluciones->getCollection()->each->setRelation('expediente', null);
        }

        return view('livewire.resoluciones', [
            'resolucionesConExpediente' => $resoluciones,
            'tiposPresentes' => $tiposPresentes,
            'total' => $total,
        ]);
    }
}
