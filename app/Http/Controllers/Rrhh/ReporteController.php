<?php

namespace App\Http\Controllers\Rrhh;

use App\Enums\EstadoDia;
use App\Http\Controllers\Controller;
use App\Services\Rrhh\Alcance;
use App\Services\Rrhh\AlcanceFactory;
use App\Services\Rrhh\CalculoVacaciones;
use App\Services\Rrhh\Contracts\Asistencias;
use App\Services\Rrhh\Contracts\Legajos;
use App\Services\Rrhh\Contracts\Licencias;
use App\Services\Rrhh\Hora;
use App\Services\Rrhh\Organigrama;
use App\Support\Csv;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reportes de RRHH: vista lista para imprimir / "Guardar como PDF" o, con ?formato=csv,
 * planilla para Excel (BOM + punto y coma). Solo RRHH.
 */
class ReporteController extends Controller
{
    public const REPORTES = [
        'ficha' => 'Ficha personal',
        'presentismo' => 'Presentismo mensual por oficina',
        'adeudadas' => 'Vacaciones adeudadas',
        'licencias' => 'Licencias por tipo',
    ];

    public function __construct(
        private Legajos $legajos,
        private Asistencias $asistencias,
        private Licencias $licencias,
        private Organigrama $organigrama,
    ) {}

    public function __invoke(Request $request, string $reporte, AlcanceFactory $alcances): Response
    {
        abort_unless(array_key_exists($reporte, self::REPORTES), 404);
        $alcance = $alcances->para($request->user());
        abort_unless($alcance->rrhh, 403);

        if ($reporte === 'ficha') {
            return response()->view('rrhh.reportes.ficha', $this->ficha($request, $alcance));
        }

        $oficinaId = $request->integer('oficina') ?: null;
        $oficina = $oficinaId ? $this->organigrama->oficinas()->firstWhere('id', $oficinaId)?->nombre : null;
        [$subtitulo, $columnas, $filas] = match ($reporte) {
            'presentismo' => $this->presentismo($request, $alcance, $oficinaId, $oficina),
            'adeudadas' => $this->adeudadas($alcance, $oficinaId, $oficina),
            'licencias' => $this->porTipo($request, $alcance, $oficinaId, $oficina),
        };

        if ($request->query('formato') === 'csv') {
            return $this->csv($reporte, $columnas, $filas);
        }

        return response()->view('rrhh.reportes.tabla', [
            'titulo' => self::REPORTES[$reporte],
            'subtitulo' => $subtitulo,
            'columnas' => $columnas,
            'filas' => $filas,
        ]);
    }

    /** @return array<string, mixed> */
    private function ficha(Request $request, Alcance $alcance): array
    {
        $legajo = (string) $request->query('legajo', '');
        abort_unless(ctype_digit($legajo), 404);

        $empleado = collect($this->legajos->buscar($alcance, ['texto' => $legajo, 'estado' => 'todos'], 1)->items())->first();
        abort_if($empleado === null || (string) $empleado->legajo !== $legajo, 404);

        $ficha = $this->legajos->ficha($alcance, $empleado->id);

        return $ficha + [
            'vacaciones' => $this->licencias->vacaciones($alcance, $empleado->id),
            'licencias' => $this->licencias->deEmpleado($alcance, $empleado->id, Hora::hoy()->year),
        ];
    }

    private function presentismo(Request $request, Alcance $alcance, ?int $oficinaId, ?string $oficina): array
    {
        abort_if($oficinaId === null, 422, 'Elegí una oficina.');
        $mes = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->query('mes'))
            ? CarbonImmutable::createFromFormat('!Y-m', $request->query('mes'))
            : Hora::hoy()->startOfMonth();

        $grilla = $this->asistencias->grilla($alcance, $mes->year, $mes->month, $oficinaId);
        $contar = fn (array $dias, EstadoDia $e) => count(array_filter($dias, fn ($d) => $d->estado === $e));

        $filas = array_map(fn ($fila) => [
            $fila['empleado']->legajo,
            $fila['empleado']->apellido.', '.$fila['empleado']->nombre,
            $contar($fila['dias'], EstadoDia::Presente),
            $contar($fila['dias'], EstadoDia::Tarde),
            $contar($fila['dias'], EstadoDia::Incompleto),
            $contar($fila['dias'], EstadoDia::Falta),
            $contar($fila['dias'], EstadoDia::Justificado),
            $contar($fila['dias'], EstadoDia::Licencia),
        ], $grilla['filas']);

        return [
            $oficina.' · '.$mes->translatedFormat('F Y'),
            ['Legajo', 'Apellido y nombre', 'Presente', 'Tarde', 'Incompleto', 'Falta', 'Justificado', 'Licencia'],
            $filas,
        ];
    }

    private function adeudadas(Alcance $alcance, ?int $oficinaId, ?string $oficina): array
    {
        $filas = $this->licencias->adeudadas($alcance, $oficinaId)->map(fn ($f) => [
            $f['empleado']->legajo,
            $f['empleado']->apellido.', '.$f['empleado']->nombre,
            $f['empleado']->oficinaRrhh->nombre ?? '',
            $f['vacaciones']->corresponden,
            $f['vacaciones']->tomadas + $f['vacaciones']->anticipadas,
            $f['vacaciones']->pendientes,
        ])->all();

        return [
            ($oficina ?? 'Todas las oficinas').' · período '.CalculoVacaciones::periodoVigente(),
            ['Legajo', 'Apellido y nombre', 'Oficina', 'Corresponden', 'Tomadas', 'Pendientes'],
            $filas,
        ];
    }

    private function porTipo(Request $request, Alcance $alcance, ?int $oficinaId, ?string $oficina): array
    {
        $fecha = fn (string $campo, CarbonImmutable $defecto) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query($campo))
            ? CarbonImmutable::parse($request->query($campo))
            : $defecto;
        $desde = $fecha('desde', Hora::hoy()->startOfYear());
        $hasta = $fecha('hasta', Hora::hoy());

        $filas = $this->licencias->porTipo($alcance, $desde, $hasta, $oficinaId)
            ->map(fn ($t) => [$t->tipo, $t->cantidad, $t->dias])->all();

        return [
            ($oficina ?? 'Todas las oficinas').' · '.$desde->format('d/m/Y').' al '.$hasta->format('d/m/Y'),
            ['Tipo de licencia', 'Cantidad', 'Días (dentro del período)'],
            $filas,
        ];
    }

    private function csv(string $reporte, array $columnas, array $filas): Response
    {
        return response()->streamDownload(function () use ($columnas, $filas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, $columnas, ';');
            foreach ($filas as $fila) {
                fputcsv($salida, Csv::fila($fila), ';');
            }
            fclose($salida);
        }, "rrhh-$reporte-".Hora::hoy()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
