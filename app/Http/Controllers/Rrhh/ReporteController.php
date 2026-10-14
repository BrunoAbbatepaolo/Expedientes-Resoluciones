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
        'inasistencias' => 'Inasistencias por empleado',
        'cumpleanios' => 'Cumpleaños del mes',
        'antiguedad' => 'Antigüedad del personal',
        'personal' => 'Listado de personal',
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
            'inasistencias' => $this->inasistencias($request, $alcance, $oficinaId, $oficina),
            'cumpleanios' => $this->cumpleanios($request, $alcance),
            'antiguedad' => $this->antiguedad($request, $alcance),
            'personal' => $this->personal($request, $alcance, $oficinaId, $oficina),
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

    /** Fecha Y-m-d válida del query o el valor por defecto. */
    private function fecha(Request $request, string $campo, CarbonImmutable $defecto): CarbonImmutable
    {
        $valor = (string) $request->query($campo);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) && CarbonImmutable::hasFormat($valor, 'Y-m-d') ? CarbonImmutable::parse($valor) : $defecto;
    }

    private function inasistencias(Request $request, Alcance $alcance, ?int $oficinaId, ?string $oficina): array
    {
        $desde = $this->fecha($request, 'desde', Hora::hoy()->startOfYear());
        $hasta = $this->fecha($request, 'hasta', Hora::hoy());
        abort_if($desde->gt($hasta) || $desde->diffInDays($hasta) > 366, 422, 'El período no es válido (máximo un año).');

        $filas = $this->licencias->inasistenciasEntre($alcance, $desde, $hasta, $oficinaId)
            ->map(fn ($f) => [$f->legajo, $f->apellido.', '.$f->nombre, $f->oficina, $f->tipo, $f->cantidad])->all();

        return [
            ($oficina ?? 'Todas las oficinas').' · '.$desde->format('d/m/Y').' al '.$hasta->format('d/m/Y'),
            ['Legajo', 'Apellido y nombre', 'Oficina', 'Tipo', 'Cantidad'],
            $filas,
        ];
    }

    private function cumpleanios(Request $request, Alcance $alcance): array
    {
        $mes = $request->integer('mes') ?: Hora::hoy()->month;
        abort_unless($mes >= 1 && $mes <= 12, 422, 'Mes inválido.');

        $filas = $this->legajos->cumpleanios($alcance, $mes)
            ->map(fn ($f) => [$f->dia, $f->legajo, $f->apellido.', '.$f->nombre, $f->oficina])->all();

        return [
            ucfirst(CarbonImmutable::create(2000, $mes, 1)->locale('es')->translatedFormat('F')),
            ['Día', 'Legajo', 'Apellido y nombre', 'Oficina'],
            $filas,
        ];
    }

    private function antiguedad(Request $request, Alcance $alcance): array
    {
        $al = $this->fecha($request, 'al', Hora::hoy());
        $situacionId = $request->integer('situacion') ?: null;
        $situacion = $situacionId ? ($this->legajos->situaciones()[$situacionId] ?? null) : null;
        abort_if($situacionId && $situacion === null, 404);

        $filas = $this->legajos->antiguedad($alcance, $al, $situacionId)
            ->map(fn ($f) => [$f->legajo, $f->apellido.', '.$f->nombre, $f->situacion, $f->ingreso->format('d/m/Y'), $f->antiguedad_ipv, $f->antiguedad_total])->all();

        return [
            ($situacion ?? 'Todas las situaciones').' · al '.$al->format('d/m/Y'),
            ['Legajo', 'Apellido y nombre', 'Situación', 'Ingreso', 'Antigüedad en el IPV', 'Antigüedad total (con reconocimientos)'],
            $filas,
        ];
    }

    private function personal(Request $request, Alcance $alcance, ?int $oficinaId, ?string $oficina): array
    {
        $estado = in_array($request->query('estado'), ['activos', 'pasivos', 'todos'], true) ? $request->query('estado') : 'activos';

        $filas = collect($this->legajos->buscar($alcance, ['estado' => $estado, 'oficina_id' => $oficinaId], 5000)->items())
            ->map(fn ($e) => [
                $e->legajo,
                $e->apellido.', '.$e->nombre,
                $e->oficinaRrhh->nombre ?? '',
                $e->situacionLaboral->nombre ?? '',
                $e->fecha_ingreso?->format('d/m/Y') ?? '',
                $e->espasivo ? 'Pasivo' : 'Activo',
            ])->all();

        return [
            ($oficina ?? 'Todas las oficinas').' · '.$estado,
            ['Legajo', 'Apellido y nombre', 'Oficina', 'Situación', 'Ingreso', 'Estado'],
            $filas,
        ];
    }

    private function porTipo(Request $request, Alcance $alcance, ?int $oficinaId, ?string $oficina): array
    {
        $desde = $this->fecha($request, 'desde', Hora::hoy()->startOfYear());
        $hasta = $this->fecha($request, 'hasta', Hora::hoy());

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
