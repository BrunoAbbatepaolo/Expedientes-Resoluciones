<?php

namespace App\Livewire;

use App\Services\Resoluciones\Tipos;
use App\Traits\AuthorizesOficina;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ElegirResolucion extends Component
{
    use AuthorizesOficina;

    /**
     * Cómo se agrupan en la pantalla (por lo que la persona quiere hacer) y la ayuda de cada tipo.
     * Un tipo nuevo de Tipos::TODOS que no esté acá cae en "Otras resoluciones" sin descripción.
     */
    private const GRUPOS = [
        'Cancelar una deuda' => ['Cancelaciones', 'Cancelacion8042', 'CancelacionFondoReserva'],
        'Cambiar la titularidad' => ['Transferencias', 'Transferencia-Cancelacion', 'Adjudicacion', 'Resciciones'],
        'Pagos y notas' => ['AplicarPagos', 'ReconocimientoCuotaPagadaDosVeces', 'ReconocimientoCuotaPagadaNoCargada'],
        'Corregir o responder' => ['Rectificacion', 'OficioJudicial'],
    ];

    private const AYUDA = [
        'Cancelaciones' => 'Cancela la deuda de una vivienda ya pagada.',
        'Cancelacion8042' => 'Cancelación de deuda amparada por la Ley 8042.',
        'CancelacionFondoReserva' => 'Cancelación cubierta por el Fondo de Reserva.',
        'Transferencias' => 'Pasa la vivienda a un nuevo titular.',
        'Transferencia-Cancelacion' => 'Transfiere la vivienda y cancela la deuda.',
        'Adjudicacion' => 'Adjudica una vivienda a su titular.',
        'Resciciones' => 'Deja sin efecto la adjudicación.',
        'AplicarPagos' => 'Nota para aplicar un pago al plan.',
        'ReconocimientoCuotaPagadaDosVeces' => 'Reconoce una cuota abonada por duplicado.',
        'ReconocimientoCuotaPagadaNoCargada' => 'Reconoce una cuota pagada que no figura.',
        'Rectificacion' => 'Corrige datos de una resolución anterior.',
        'OficioJudicial' => 'Toma razón de un oficio de un juzgado.',
    ];

    /** Ícono (trazos de 24x24) y color por tipo; los colores son los de la lista de resoluciones. */
    private const ICONO = [
        'Cancelaciones' => ['azul', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'Cancelacion8042' => ['azul', 'M12 3 4.5 6v5.25c0 4.5 3.2 8.2 7.5 9.75 4.3-1.55 7.5-5.25 7.5-9.75V6L12 3zM9 12l2 2 4-4'],
        'CancelacionFondoReserva' => ['azul', 'M2.25 18.75a60 60 0 0115.8-2.4m-15.8 2.4V8.25m0 10.5h.75m14.25-2.4a60 60 0 013.75.15M18 6.75h.008v.008H18V6.75zM6 9.75h.008v.008H6V9.75zM12 14.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z'],
        'Transferencias' => ['cielo', 'M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
        'Transferencia-Cancelacion' => ['cielo', 'M16.5 3.75 21 8.25m0 0-4.5 4.5M21 8.25H8.25m-1.5 12L3 15.75m0 0 3.75-4.5M3 15.75h12.75'],
        'Adjudicacion' => ['cielo', 'M2.25 12 12 3l9.75 9M4.5 9.75V20.25h5.25v-5.25h4.5v5.25h5.25V9.75'],
        'Resciciones' => ['magenta', 'M6 18 18 6M6 6l12 12'],
        'AplicarPagos' => ['verde', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
        'ReconocimientoCuotaPagadaDosVeces' => ['verde', 'M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9 9 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9 9 0 00-.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75'],
        'ReconocimientoCuotaPagadaNoCargada' => ['verde', 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'],
        'Rectificacion' => ['oro', 'm16.862 4.487 1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125'],
        'OficioJudicial' => ['oro', 'M12 3v18M3.75 7.5h16.5M6 7.5 3 15a3 3 0 006 0L6 7.5zm12 0L15 15a3 3 0 006 0l-3-7.5z'],
    ];

    /** @var list<array{titulo: string, tipos: list<array<string, mixed>>}> */
    public array $grupos = [];

    public string $busqueda = '';

    public function mount()
    {
        $this->autorizarPermiso('resolucion_editar');

        $agrupados = array_merge(...array_values(self::GRUPOS));
        $sueltos = array_values(array_diff(Tipos::TODOS, $agrupados));

        $armar = fn (string $tipo) => [
            'nombre' => $tipo,
            'display' => Tipos::nombre($tipo),
            'ayuda' => self::AYUDA[$tipo] ?? '',
            'acento' => self::ICONO[$tipo][0] ?? 'gris',
            'icono' => self::ICONO[$tipo][1] ?? 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
            // Sin plantilla completa el tipo igual se puede usar en modo "Personalizado".
            'plantillaDisponible' => View::exists("prototipos.{$tipo}"),
        ];

        $grupos = [];
        foreach ([...self::GRUPOS, 'Otras resoluciones' => $sueltos] as $titulo => $tipos) {
            if ($tipos !== []) {
                $grupos[] = ['titulo' => $titulo, 'tipos' => array_map($armar, $tipos)];
            }
        }
        $this->grupos = $grupos;
    }

    /** Los grupos con solo los tipos que coinciden con la búsqueda (por nombre, no por descripción). */
    #[Computed]
    public function gruposFiltrados(): array
    {
        $q = mb_strtolower(trim($this->busqueda));
        if ($q === '') {
            return $this->grupos;
        }

        $filtrados = [];
        foreach ($this->grupos as $grupo) {
            $tipos = array_values(array_filter(
                $grupo['tipos'],
                fn (array $t) => str_contains(mb_strtolower($t['nombre'].' '.$t['display'].' '.$t['ayuda']), $q),
            ));
            if ($tipos !== []) {
                $filtrados[] = ['titulo' => $grupo['titulo'], 'tipos' => $tipos];
            }
        }

        return $filtrados;
    }

    public function render()
    {
        return view('livewire.elegir-resolucion');
    }
}
