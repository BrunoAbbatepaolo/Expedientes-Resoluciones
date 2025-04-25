<?php

namespace App\Livewire\Forms;

use App\Models\Resolucion;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ResolucionForm extends Form
{
    public ?Resolucion $resolucion;

    #[Validate('required')]
    public $numero_exp;

    #[Validate('required')]
    public $numero_resolucion;

    #[Validate('required')]
    public $fecha;

    #[Validate('required')]
    public $cod_barrio;

    #[Validate('required')]
    public $cod_casa;

    #[Validate('required')]
    public $pdf;

    public $campos = [
        'numero_exp',
        'numero_resolucion',
        'fecha',
        'cod_barrio',
        'cod_casa',
        'pdf',
    ];

    public function loadResolucion(Resolucion $resolucion)
    {
        $this->resolucion = $resolucion;

        $this->numero_exp = $resolucion->numero_exp;
        $this->numero_resolucion = $resolucion->numero_resolucion;
        $this->fecha = $resolucion->fecha;
        $this->cod_barrio = $resolucion->cod_barrio;
        $this->cod_casa = $resolucion->cod_casa;
        $this->pdf = $resolucion->pdf;
    }

    public function store()
    {
        $this->validate();
        Resolucion::create($this->except('campos'));
    }

    public function update()
    {
        $this->validate();
        Resolucion::update($this->except('campos'));
    }
}
