<?php

namespace App\Livewire\Forms;

use App\Models\Resolucion;
use App\Services\ExpedientesIpv;
use Illuminate\Validation\ValidationException;
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

    // Opcional: el modal de edición no lo muestra (antes era obligatorio y "Guardar cambios" fallaba sin aviso).
    #[Validate('nullable|date')]
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

        // Una resolución siempre va vinculada a un expediente real. Si no se cambió el número no se vuelve a
        // exigir (hay resoluciones viejas cargadas antes de esta regla que igual se tienen que poder editar).
        $numero = trim((string) $this->numero_exp);
        if ($numero !== $this->resolucion->numero_exp) {
            try {
                $existe = app(ExpedientesIpv::class)->resumenPorNumero($numero) !== null;
            } catch (\Throwable $e) {
                report($e);

                throw ValidationException::withMessages(['resolucionForm.numero_exp' => 'No se pudo verificar el expediente en este momento. Probá de nuevo en unos minutos.']);
            }

            if (! $existe) {
                throw ValidationException::withMessages(['resolucionForm.numero_exp' => "El expediente {$numero} no existe. Una resolución tiene que estar vinculada a un expediente cargado."]);
            }
        }

        $datos = collect($this->only($this->editables))
            ->map(fn ($valor) => $valor === '' ? null : $valor)
            ->all();

        if ($this->pdf) {
            // Disco privado: se sirve por la ruta autorizada 'resoluciones.pdf'.
            $datos['pdf'] = $this->pdf->store('resoluciones', 'local');
        }

        // Las de "Plantilla completa" se imprimen desde datos.campos: se actualizan los mismos datos ahí.
        /** @var array{campos?: array<string, mixed>}|null $guardados cast 'array' del modelo */
        $guardados = $this->resolucion->datos;
        if (is_array($guardados) && isset($guardados['campos'])) {
            $guardados['campos'] = array_merge($guardados['campos'], array_filter([
                'num_exp' => $datos['numero_exp'] ?? null,
                'num_res' => $datos['numero_resolucion'] ?? null,
                'fecha_res' => $datos['fecha'] ?? null,
            ], fn ($v) => $v !== null));
            $datos['datos'] = $guardados;
        }

        $this->resolucion->update($datos);
    }
}
