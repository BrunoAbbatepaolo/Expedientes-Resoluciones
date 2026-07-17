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
        DB::connection('mysql_admin')->beginTransaction();
        try {
            $data = collect($this->campos)
                ->mapWithKeys(fn ($campo) => [$campo => $this->{$campo}])
                ->map(fn ($valor) => $valor === '' ? null : $valor)
                ->toArray();

            \App\Models\Expediente::create($data);
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
                $oficinaOrigenId = $this->expediente->oficina_id;

                $data = collect($this->campos)
                    ->mapWithKeys(fn ($campo) => [$campo => $this->{$campo}])
                    ->map(fn ($valor) => $valor === '' ? null : $valor)
                    ->toArray();

                $this->expediente->update($data);

                // Si se eligió una oficina de destino distinta de la actual, es un pase real:
                // dejar registro en el historial (antes solo se pisaban los campos del expediente).
                if ($this->ofi_salida && (int) $this->ofi_salida !== (int) $oficinaOrigenId) {
                    \App\Models\Pase::create([
                        'expediente_id' => $this->expediente->id,
                        'oficina_id' => $this->ofi_salida,
                        'oficina_origen_id' => $oficinaOrigenId,
                        'fecha' => $this->fecha_salida ?: now()->toDateString(),
                        'user_id' => auth()->id(),
                        'importado' => false,
                        'firmado' => false,
                    ]);
                }

                DB::connection('mysql_admin')->commit();

                return 1;
            } catch (\Exception $exception) {
                DB::connection('mysql_admin')->rollBack();

                return 0;
            }
        }

        return -1;
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
