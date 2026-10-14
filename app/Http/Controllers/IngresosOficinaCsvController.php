<?php

namespace App\Http\Controllers;

use App\Services\ExpedientesIpv;
use App\Support\Csv;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** CSV (Excel: BOM + punto y coma) del informe "Ingresos a oficina". Mismo permiso que la pantalla. */
class IngresosOficinaCsvController extends Controller
{
    public function __invoke(Request $request, ExpedientesIpv $ipv): Response
    {
        abort_unless((bool) $request->user()?->permiso('expediente_ver'), 403);

        $oficinaId = $request->query('oficina');
        abort_unless(is_string($oficinaId) && ctype_digit($oficinaId), 404);

        $desde = (string) $request->query('desde');
        $hasta = (string) $request->query('hasta');
        abort_if(ExpedientesIpv::errorDePeriodo($desde, $hasta) !== null, 422);

        $oficina = $ipv->oficinas()->firstWhere('id', (int) $oficinaId);
        abort_if($oficina === null, 404);

        ['filas' => $filas] = $ipv->ingresosAOficina((int) $oficinaId, $desde, $hasta);

        $nombre = 'ingresos-'.preg_replace('/[^a-z0-9]+/i', '-', strtolower($oficina->nombre)).'-'.$desde.'-a-'.$hasta.'.csv';

        return response()->streamDownload(function () use ($filas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Expediente', 'Causante', 'Asunto', 'Primer ingreso', 'Viene de', 'Veces que entró'], ';');

            foreach ($filas as $f) {
                fputcsv($salida, Csv::fila([
                    $f->numero,
                    $f->causante,
                    $f->asunto,
                    Carbon::parse($f->fecha_hora)->format('d/m/Y H:i'),
                    $f->viene_de ?? '',
                    $f->veces,
                ]), ';');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
