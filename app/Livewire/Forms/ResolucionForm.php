<?php

namespace App\Livewire\Forms;

use App\Models\Resolucion;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ResolucionForm extends Form
{
    public ?Resolucion $resolucion;

    #[Validate('required|string')]
    public $numero_exp;

    #[Validate('required|string')]
    public $numero_resolucion;

    #[Validate('required|date')]
    public $fecha;

    #[Validate('required|date')]
    public $fecha_ingreso;

    #[Validate('required|string')]
    public $cod_barrio;

    #[Validate('nullable|string')]
    public $cod_casa;

    #[Validate('nullable|file|mimes:pdf,jpg,jpeg|max:2048')]
    public $pdf;

    public $campos = [
        'resolucion',
        'campos',
        'pdf',
    ];

    public function loadResolucion(Resolucion $resolucion)
    {
        $this->resolucion = $resolucion;

        $this->numero_exp = $resolucion->numero_exp;
        $this->numero_resolucion = $resolucion->numero_resolucion;
        $this->fecha = $resolucion->fecha;
        $this->fecha_ingreso = $resolucion->fecha_ingreso;
        $this->cod_barrio = $resolucion->cod_barrio;
        $this->cod_casa = $resolucion->cod_casa;
    }

    public function store()
    {
        $this->validate();
        Resolucion::create($this->except($this->campos));
    }

    public function update()
    {
        $this->validate();

        if ($this->pdf) {
            $pdfPath = $this->pdf->store('resoluciones', 'public');
            $this->resolucion->update([
                'pdf' => $pdfPath
            ]);
        }
        $this->resolucion->update(
            $this->except($this->campos)
        );
    }
}
