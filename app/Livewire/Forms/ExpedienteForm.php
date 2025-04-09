<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Support\Facades\DB;

class ExpedienteForm extends Form
{
    public ?\App\Models\Expediente $expediente;

    #[Validate('required', message: 'Debe ingresar el número de expediente')]
    public $num_exp;

    #[Validate('required')]
    public $folio;

    #[Validate('required')]
    public $causante;

    #[Validate('required')]
    public $asunto;

    #[Validate('required')]
    public $fecha_ingreso;
    #[Validate('nullable')]
    public $ofi_salida;

    #[Validate('nullable')]
    public $cod_area;

    #[Validate('nullable')]
    public $cod_oficina;

    #[Validate('nullable')]
    public $fecha_salida;

    public $campos = [
        'num_exp',
        'folio',
        'causante',
        'asunto',
        'fecha_ingreso',
        'ofi_salida',
        'cod_area',
        'cod_oficina',
        'fecha_salida',
    ];

    public function loadExpMitiv($expediente)
    {
        $this->expediente = $expediente;
        foreach ($this->campos as $campo) {
            $this->{$campo} = $expediente->{$campo};
        }
    }

    public function store()
    {
        DB::beginTransaction();
        try {
            \App\Models\Expediente::create($this->except('campos'));
            DB::commit();
            return 1;
        } catch (\Exception $exception) {
            dd($exception);
            DB::rollBack();
            return 0;
        }
    }

    public function update()
    {
        if ($this->hayCambios()) {
            DB::beginTransaction();
            try {
                $this->expediente->update($this->except('campos'));
                DB::commit();
                return 1;
            } catch (\Exception $exception) {
                DB::rollBack();
                return 0;
            }
        } else {
            return -1;
        }
    }

    public function delete($expediente)
    {
    DB::beginTransaction();
    try {
          $expediente->delete();
            DB::commit();
            return 1;
        } catch (\Exception $exception) {
         DB::rollBack();
            return 0;
        }
    }
    private function hayCambios()
    {
        foreach ($this->campos as $campo) {
            if ($this->{$campo} != $this->expediente->{$campo}) {
                return true;
            }
        }
        return false;
    }
    public function fill($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
    
}
