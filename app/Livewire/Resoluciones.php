<?php

namespace App\Livewire;

use App\Livewire\Forms\ResolucionForm;
use App\Models\Resolucion;
use App\Traits\AuthorizesOficina;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithFileUploads; // Importante para controlar los modales desde el backend

class Resoluciones extends Component
{
    use AuthorizesOficina;
    use WithFileUploads;

    public ResolucionForm $resolucionForm;

    // Propiedad para saber qué registro eliminar
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
        Flux::modal('edit-profile')->close();
        Flux::toast('Resolución modificada con éxito', variant: 'success');
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

            Flux::modal('delete-profile')->close();
            Flux::toast('Resolución eliminada permanentemente', variant: 'danger');
        }
    }

    public function render()
    {
        // Se agregó latest() para que las más nuevas aparezcan arriba
        $resolucionesConExpediente = Resolucion::with('expediente')->latest()->get();

        return view('livewire.resoluciones', [
            'resolucionesConExpediente' => $resolucionesConExpediente,
        ]);
    }
}
