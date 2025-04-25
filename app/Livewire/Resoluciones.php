<?php

namespace App\Livewire;

use App\Livewire\Forms\ResolucionForm;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Resolucion;

class Resoluciones extends Component
{
    use WithFileUploads;

    public ResolucionForm $resolucionForm;

    public $numero_exp, $numero_resolucion, $fecha, $cod_barrio, $cod_casa, $pdf;
    public $showModal = false; // Cambiado de modalOpen a showModal para mayor claridad con Alpine

    protected $rules = [
        'numero_exp' => 'required|string',
        'numero_resolucion' => 'required|string',
        'fecha' => 'required|date',
        'cod_barrio' => 'required|string',
        'cod_casa' => 'required|string',
        'pdf' => 'required|file|mimes:pdf|max:2048', // Máx 2MB
    ];




    public function abrirModal()
    {
        // Resetear solo los campos necesarios, no todo el componente
        $this->numero_exp = '';
        $this->numero_resolucion = '';
        $this->fecha = '';
        $this->cod_barrio = '';
        $this->cod_casa = '';
        $this->pdf = null;

        $this->showModal = true;
    }

    public function cerrarModal()
    {
        $this->showModal = false;
    }

    public function guardar()
    {
        //$this->validate();

        $pdfPath = $this->pdf->store('resoluciones', 'public'); // Guarda en storage/app/public/resoluciones

        Resolucion::create([
            'numero_exp' => $this->numero_exp,
            'numero_resolucion' => $this->numero_resolucion,
            'fecha' => $this->fecha,
            'cod_barrio' => $this->cod_barrio,
            'cod_casa' => $this->cod_casa,
            'pdf' => $pdfPath, // Guarda la ruta en la BD
        ]);

        /*$this->resolucionForm->pdf = $this->pdf->store('resoluciones', 'public'); // Guarda en storage/app/public/resoluciones
        $this->resolucionForm->store();*/


        $this->reset(); // Limpia los campos
        $this->cerrarModal();
        session()->flash('message', 'Resolución subida con éxito');
    }

    public function render()
    {
        // Obtenemos todas las resoluciones independientemente del estado del modal
        $resolucionesConExpediente = Resolucion::with('expediente')->get();

        return view('livewire.resoluciones', [
            'resolucionesConExpediente' => $resolucionesConExpediente
        ]);
    }

    /**
     * Carga los datos de una resolución en $resolucionForm dejándolos disponibles para su modificación.
     * 
     */
    public function cargarResolucion($id)
    {
        $resolucion = Resolucion::find($id);
        $this->resolucionForm->loadResolucion($resolucion);
    }
}
