<?php

namespace App\Http\Controllers;

use App\Services\ExpedientesIpv;
use App\Support\Csv;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Salidas de los informes de expedientes del IPV (mitivdb, solo lectura): impresión A4 de
 * "Ingresos a oficina" y "Pases emitidos", y CSV de "Pases emitidos". Mismo permiso que las pantallas.
 */
class InformesExpedientesController extends Controller
{
    public function __construct(private ExpedientesIpv $ipv) {}

    public function ingresosImprimir(Request $request): View
    {
        $this->exigirPermiso($request);
        [$desde, $hasta] = $this->periodo($request);
        $oficina = $this->oficina($request->query('oficina'), obligatoria: true);

        ['filas' => $filas, 'truncado' => $truncado] = $this->ipv->ingresosAOficina($oficina->id, $desde, $hasta);

        return view('expedientes.imprimir-ingresos', compact('filas', 'truncado', 'oficina', 'desde', 'hasta'));
    }

    public function pasesImprimir(Request $request): View
    {
        $this->exigirPermiso($request);
        [$desde, $hasta] = $this->periodo($request);
        $origen = $this->oficina($request->query('origen'), obligatoria: false);

        ['filas' => $filas, 'truncado' => $truncado] = $this->ipv->pasesEmitidos($origen?->id, $desde, $hasta);

        return view('expedientes.imprimir-pases', compact('filas', 'truncado', 'origen', 'desde', 'hasta'));
    }

    public function pasesCsv(Request $request): Response
    {
        $this->exigirPermiso($request);
        [$desde, $hasta] = $this->periodo($request);
        $origen = $this->oficina($request->query('origen'), obligatoria: false);

        ['filas' => $filas] = $this->ipv->pasesEmitidos($origen?->id, $desde, $hasta);

        $nombre = 'pases-emitidos-'.($origen ? preg_replace('/[^a-z0-9]+/i', '-', strtolower($origen->nombre)).'-' : '').$desde.'-a-'.$hasta.'.csv';

        return response()->streamDownload(function () use ($filas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Fecha', 'Expediente', 'Causante', 'Asunto', 'Origen', 'Destino', 'Lo pasó', 'Observación'], ';');

            foreach ($filas as $f) {
                fputcsv($salida, Csv::fila([
                    Carbon::parse($f->fecha_hora)->format('d/m/Y H:i'),
                    $f->numero,
                    $f->causante,
                    $f->asunto,
                    $f->origen,
                    $f->destino ?? '',
                    trim(($f->apellido ?? '').' '.($f->nombre_empleado ?? '')),
                    $f->observacion ?? '',
                ]), ';');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function exigirPermiso(Request $request): void
    {
        abort_unless((bool) $request->user()?->permiso('expediente_ver'), 403);
    }

    /** @return array{0: string, 1: string} */
    private function periodo(Request $request): array
    {
        $desde = (string) $request->query('desde');
        $hasta = (string) $request->query('hasta');
        abort_if(ExpedientesIpv::errorDePeriodo($desde, $hasta) !== null, 422);

        return [$desde, $hasta];
    }

    private function oficina(mixed $id, bool $obligatoria): ?object
    {
        if (! $obligatoria && ($id === null || $id === '')) {
            return null;
        }
        abort_unless(is_string($id) && ctype_digit($id), 404);

        $oficina = $this->ipv->oficinas()->firstWhere('id', (int) $id);
        abort_if($oficina === null, 404);

        return $oficina;
    }
}
