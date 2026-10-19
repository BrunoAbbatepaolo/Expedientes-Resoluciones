<?php

namespace App\Livewire;

use App\Livewire\Forms\ResolucionForm;
use App\Models\Resolucion;
use App\Traits\AuthorizesOficina;
use App\Traits\NotificaUi;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Resoluciones extends Component
{
    use AuthorizesOficina;
    use NotificaUi;
    use WithFileUploads;

    public ResolucionForm $resolucionForm;

    /** Búsqueda por número de expediente, de resolución o barrio/casa. */
    #[Url(as: 'q', except: '')]
    public string $buscar = '';

    /** Filtro por tipo (CrearResolucion::TIPOS) o 'otras' (las cargadas sin tipo). */
    #[Url(except: '')]
    public string $tipo = '';

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
    }

    public function render()
    {
        // Sin permiso no se consulta nada (la vista ya muestra el aviso).
        if (! auth()->user()?->permiso('resolucion_ver')) {
            return view('livewire.resoluciones', ['resolucionesConExpediente' => collect(), 'tiposPresentes' => collect(), 'total' => 0]);
        }

        $oficinaId = auth()->user()->oficinaAsignadaId()
            ?? auth()->user()->oficinaIdPara('resolucion_ver');

        // Se agregó latest() para que las más nuevas aparezcan arriba.
        // Las resoluciones viejas sin oficina_id (ver implementacion_futuro.md 2.1)
        // se siguen mostrando a todos hasta que se les asigne una oficina.
        $visibles = Resolucion::query()->where(function ($query) use ($oficinaId) {
            $query->whereNull('oficina_id');

            if ($oficinaId) {
                $query->orWhere('oficina_id', $oficinaId);
            }
        });

        // Tipos presentes (para los filtros) y total, antes de aplicar la búsqueda.
        $tiposPresentes = (clone $visibles)->select('tipo')->distinct()->pluck('tipo')
            ->map(fn ($t) => $t ?: 'otras')->unique()->sort()->values();
        $total = (clone $visibles)->count();

        $consulta = $visibles->with('archivos')->latest();
        if ($this->tipo === 'otras') {
            $consulta->whereNull('tipo');
        } elseif (in_array($this->tipo, CrearResolucion::TIPOS, true)) {
            $consulta->where('tipo', $this->tipo);
        }
        if (trim($this->buscar) !== '') {
            $termino = '%'.addcslashes(trim($this->buscar), '%_\\').'%';
            $consulta->where(fn ($q) => $q->where('numero_exp', 'like', $termino)
                ->orWhere('numero_resolucion', 'like', $termino)
                ->orWhere('cod_barrio', 'like', $termino)
                ->orWhere('cod_casa', 'like', $termino));
        }
        $resolucionesConExpediente = $consulta->get();

        // 'expediente' viene de pgsql_mitiv (otro servidor): si no responde, se
        // muestra el listado igual, sin el número de expediente.
        try {
            $resolucionesConExpediente->load('expediente');
        } catch (\Throwable $e) {
            report($e);
            $resolucionesConExpediente->each->setRelation('expediente', null);
        }

        return view('livewire.resoluciones', [
            'resolucionesConExpediente' => $resolucionesConExpediente,
            'tiposPresentes' => $tiposPresentes,
            'total' => $total,
        ]);
    }
}
