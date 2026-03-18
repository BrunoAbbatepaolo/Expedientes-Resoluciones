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
        DB::connection('mysql_admin')->beginTransaction();
        try {
            $data = collect($this->campos)
                ->mapWithKeys(fn($campo) => [$campo => $this->{$campo}])
                ->map(fn($valor) => $valor === '' ? null : $valor)
                ->toArray();

            // Agregar timestamps manualmente
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::connection('mysql_admin')
                ->table('expedientes')
                ->insert($data);

            DB::connection('mysql_admin')->commit();
            return 1;
        } catch (\Exception $exception) {
            DB::connection('mysql_admin')->rollBack();
            return 0;
        }
    }

    public function update()
    {
        if ($this->hayCambios()) {
            DB::connection('mysql_admin')->beginTransaction();
            try {
                $data = collect($this->campos)
                    ->mapWithKeys(fn($campo) => [$campo => $this->{$campo}])
                    ->map(fn($valor) => $valor === '' ? null : $valor)
                    ->toArray();

                DB::connection('mysql_admin')
                    ->table('expedientes')
                    ->where('id', $this->expediente->id)
                    ->update($data);

                DB::connection('mysql_admin')->commit();
                return 1;
            } catch (\Exception $exception) {
                DB::connection('mysql_admin')->rollBack();
                return 0;
            }
        }
        return -1;
    }

    public function delete($expediente)
    {
        DB::connection('mysql_admin')->beginTransaction();
        try {
            $expediente->delete();
            DB::connection('mysql_admin')->commit();
            return 1;
        } catch (\Exception $exception) {
            DB::connection('mysql_admin')->rollBack();
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