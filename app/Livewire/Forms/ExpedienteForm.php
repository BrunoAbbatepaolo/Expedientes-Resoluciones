<?php

namespace App\Livewire\Forms;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Form;

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

    #[Validate('required')]
    public $oficina_id;

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
        'oficina_id',
    ];

    public function loadExpMitiv($expediente)
    {
        $this->expediente = $expediente;
        foreach ($this->campos as $campo) {
            $this->{$campo} = $expediente->{$campo} ?? null;
        }
    }

    public function store()
    {
        try {
            return DB::connection('mysql_admin')->transaction(function () {
                $data = collect($this->campos)
                    ->mapWithKeys(fn ($campo) => [$campo => $this->{$campo}])
                    ->map(fn ($valor) => $valor === '' ? null : $valor)
                    ->toArray();

                \App\Models\Expediente::create($data);

                return 1;
            });
        } catch (\Exception $exception) {
            return 0;
        }
    }

    public function update()
    {
        if (! $this->hayCambios()) {
            return -1;
        }

        try {
            return DB::connection('mysql_admin')->transaction(function () {
                // lockForUpdate: si dos usuarios editan el mismo expediente a la vez, el
                // segundo espera a que termine el primero.
                $expediente = \App\Models\Expediente::lockForUpdate()->findOrFail($this->expediente->id);

                $data = collect($this->campos)
                    ->mapWithKeys(fn ($campo) => [$campo => $this->{$campo}])
                    ->map(fn ($valor) => $valor === '' ? null : $valor)
                    ->toArray();

                $expediente->update($data);
                $this->expediente = $expediente;

                // Iniciar un pase (elegir oficina de destino) es una acción aparte,
                // ver Expedientes::confirmarPase() — este método solo edita los datos
                // propios del expediente, no crea historial de traslados.
                return 1;
            });
        } catch (\Exception $exception) {
            return 0;
        }
    }

    public function hayCambios()
    {
        foreach ($this->campos as $campo) {
            $valorForm = $this->{$campo} === '' ? null : $this->{$campo};
            $valorModelo = $this->expediente->{$campo};
            if ($valorForm != $valorModelo) {
                return true;
            }
        }

        return false;
    }
}
