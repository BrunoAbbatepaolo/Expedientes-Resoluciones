<?php

namespace App\Http\Controllers;

use App\Services\Resoluciones\Tipos;
use App\Models\Resolucion;
use App\Support\Csv;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Planilla (CSV para Excel) con las resoluciones que ve la persona, con la misma búsqueda y filtro de la lista. */
class ExportarResolucionesController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $usuario = $request->user();
        abort_unless($usuario?->permiso('resolucion_ver'), 403);

        $buscar = (string) $request->query('q', '');
        $tipo = (string) $request->query('tipo', '');
        $tipo = $tipo === 'otras' || in_array($tipo, Tipos::TODOS, true) ? $tipo : '';

        $consulta = Resolucion::query()->visiblesPara($usuario)->filtrar($buscar, $tipo)->latest();

        return response()->streamDownload(function () use ($consulta) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Expediente', 'N° de resolución', 'Tipo', 'Fecha', 'Manzana/Barrio', 'Casa/Lote'], ';');

            $consulta->lazyById(500)->each(function (Resolucion $r) use ($salida) {
                fputcsv($salida, Csv::fila([
                    $r->numero_exp,
                    $r->numero_resolucion ?? '',
                    $r->tipo ? Tipos::nombre($r->tipo) : 'Sin tipo',
                    $r->fecha ? \Illuminate\Support\Carbon::parse($r->fecha)->format('d/m/Y') : '',
                    $r->cod_barrio ?? '',
                    $r->cod_casa ?? '',
                ]), ';');
            });

            fclose($salida);
        }, 'resoluciones-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
