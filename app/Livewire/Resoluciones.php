<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Resolucion;
use App\Livewire\Forms\ResolucionForm;
use Flux\Flux; // Importante para controlar los modales desde el backend

class Resoluciones extends Component
{
    use WithFileUploads;

    public ResolucionForm $resolucionForm;

    // Propiedades para la creación
    public $numero_exp, $numero_resolucion, $fecha, $fecha_ingreso, $cod_barrio, $cod_casa, $pdf;

    // Propiedad para saber qué registro eliminar
    public $resolucionIdParaBorrar;

    protected $rules = [
        'numero_exp' => 'required|string',
        'numero_resolucion' => 'required|string',
        'fecha' => 'required|date',
        'fecha_ingreso' => 'required|date',
        'cod_barrio' => 'required|string',
        'cod_casa' => 'nullable|string', // Cambiado a nullable porque dijiste que es opcional
        'pdf' => 'required|file|mimes:pdf,jpg,jpeg|max:2048', // Soporta PDF y JPG
    ];

    /**
     * Prepara el modal de Creación
     */
    public function abrirModal()
    {
        $this->resetValidation();
        $this->reset(['numero_exp', 'numero_resolucion', 'fecha', 'cod_barrio', 'cod_casa', 'pdf']);

        // Asignamos la fecha y hora actual automáticamente (formato requerido por datetime-local)
        $this->fecha_ingreso = now()->format('Y-m-d\TH:i');
    }

    /**
     * Guarda una NUEVA resolución
     */
    public function guardar()
    {
        // Le decimos a Livewire que valide SOLO estas variables
        // y que ignore el $resolucionForm
        $this->validate([
            'numero_exp' => 'required|string',
            'numero_resolucion' => 'required|string',
            'fecha' => 'required|date',
            'fecha_ingreso' => 'required|date',
            'cod_barrio' => 'required|string',
            'cod_casa' => 'nullable|string',
            'pdf' => 'required|file|mimes:pdf,jpg,jpeg|max:2048',
        ]);

        $pdfPath = $this->pdf->store('resoluciones', 'public');

        Resolucion::create([
            'numero_exp' => $this->numero_exp,
            'numero_resolucion' => $this->numero_resolucion,
            'fecha' => $this->fecha,
            'fecha_ingreso' => $this->fecha_ingreso,
            'cod_barrio' => $this->cod_barrio,
            'cod_casa' => $this->cod_casa,
            'pdf' => $pdfPath,
        ]);

        $this->reset(['numero_exp', 'numero_resolucion', 'fecha', 'fecha_ingreso', 'cod_barrio', 'cod_casa', 'pdf']);

        Flux::modal('add-resolucion')->close();
        Flux::toast('Resolución subida con éxito', variant: 'success');
    }

    /**
     * Prepara el modal de Edición cargando los datos
     */
    public function cargarResolucion($id)
    {
        $this->resetValidation();
        $resolucion = Resolucion::findOrFail($id);

        // Pasamos la data al Form Object
        $this->resolucionForm->loadResolucion($resolucion);
    }

    /**
     * Guarda los cambios de la EDICIÓN
     */
    public function guardarEdicion()
    {
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
            Resolucion::destroy($this->resolucionIdParaBorrar);
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
            'resolucionesConExpediente' => $resolucionesConExpediente
        ]);
    }
}
