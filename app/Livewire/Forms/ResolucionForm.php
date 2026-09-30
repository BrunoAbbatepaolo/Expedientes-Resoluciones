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

    // Columnas integer nullable: muchos tipos de resolución no tienen barrio/casa.
    #[Validate('nullable|integer|min:0')]
    public $cod_barrio;

    #[Validate('nullable|integer|min:0')]
    public $cod_casa;

    #[Validate('nullable|file|mimes:pdf,jpg,jpeg|max:2048')]
    public $pdf;

    // Lista explícita de lo que se actualiza (protected: no la puede tocar el cliente).
    protected array $editables = [
        'numero_exp',
        'numero_resolucion',
        'fecha',
        'fecha_ingreso',
        'cod_barrio',
        'cod_casa',
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

    public function update()
    {
        $this->validate();

        $datos = collect($this->only($this->editables))
            ->map(fn ($valor) => $valor === '' ? null : $valor)
            ->all();

        if ($this->pdf) {
            // Disco privado: se sirve por la ruta autorizada 'resoluciones.pdf'.
            $datos['pdf'] = $this->pdf->store('resoluciones', 'local');
        }

        $this->resolucion->update($datos);
    }
}
