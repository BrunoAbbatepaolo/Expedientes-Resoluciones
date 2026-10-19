<?php

namespace App\Livewire\Admin;

use App\Models\Audit;
use App\Models\Expediente;
use App\Models\Resolucion;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Panel de administración: métricas, auditoría, accesos y estado del sistema. Solo con `admin_ver`. */
#[Title('Administración')]
class PanelAdmin extends Component
{
    use WithPagination;

    public const PESTANIAS = ['resumen' => 'Resumen', 'auditoria' => 'Auditoría', 'accesos' => 'Accesos', 'firmantes' => 'Firmantes', 'sistema' => 'Sistema'];

    private const ACCESOS = ['login', 'login_fallido'];

    #[Url(as: 'tab')]
    public string $tab = 'resumen';

    #[Url]
    public string $evento = '';

    #[Url]
    public string $buscar = '';

    #[Url]
    public string $desde = '';

    #[Url]
    public string $hasta = '';

    public ?int $abierto = null;

    public function mount(): void
    {
        abort_unless((bool) auth()->user()->permiso('admin_ver'), 403);
        $this->normalizarPestania();
    }

    public function irA(string $tab): void
    {
        $this->tab = $tab;
        $this->normalizarPestania();
        $this->reset('abierto', 'evento');
        $this->resetPage();
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['evento', 'buscar', 'desde', 'hasta'], true)) {
            $this->resetPage();
            $this->abierto = null;
        }
    }

    public function limpiar(): void
    {
        $this->reset('evento', 'buscar', 'desde', 'hasta', 'abierto');
        $this->resetPage();
    }

    public function alternar(int $id): void
    {
        $this->abierto = $this->abierto === $id ? null : $id;
    }

    private function normalizarPestania(): void
    {
        if (! array_key_exists($this->tab, self::PESTANIAS)) {
            $this->tab = 'resumen';
        }
    }

    public function render(): View
    {
        // Cada request revalida el permiso: si se lo quitan con la pantalla abierta, deja de ver datos.
        abort_unless((bool) auth()->user()->permiso('admin_ver'), 403);

        $datos = match ($this->tab) {
            'auditoria' => $this->datosAuditoria(false),
            'accesos' => $this->datosAuditoria(true),
            'sistema' => ['sistema' => $this->sistema()],
            'firmantes' => [], // componente propio: Admin\Firmantes
            default => $this->resumen(),
        };

        return view('livewire.admin.panel-admin', $datos);
    }

    /** @return array<string, mixed> */
    private function resumen(): array
    {
        return Cache::remember('admin.resumen', 60, function () {
            $hoy = now()->startOfDay();
            $dias = collect(range(13, 0))->map(fn (int $atras) => now()->subDays($atras)->startOfDay());

            $porDia = Audit::where('created_at', '>=', $dias->first())
                ->whereNotIn('event', self::ACCESOS)
                ->get(['created_at'])
                ->groupBy(fn ($a) => $a->created_at->toDateString())
                ->map->count();

            $serie = $dias->map(fn ($d) => ['dia' => $d->format('d/m'), 'total' => $porDia[$d->toDateString()] ?? 0])->all();

            $activos = Audit::where('created_at', '>=', now()->subDays(7))
                ->whereNotIn('event', self::ACCESOS)
                ->whereNotNull('user_id')
                ->selectRaw('user_id, count(*) as total')
                ->groupBy('user_id')->orderByDesc('total')->limit(5)->get();
            $usuarios = User::whereIn('id', $activos->pluck('user_id'))->get()->keyBy('id');

            return [
                'metricas' => [
                    ['Usuarios', User::count()],
                    ['Expedientes activos', Expediente::whereNull('fecha_salida')->whereNull('deleted_at')->count()],
                    ['Resoluciones del mes', Resolucion::whereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()])->whereNull('deleted_at')->count()],
                    ['Cambios hoy', Audit::where('created_at', '>=', $hoy)->whereNotIn('event', self::ACCESOS)->count()],
                    ['Ingresos hoy', Audit::where('created_at', '>=', $hoy)->where('event', 'login')->count()],
                    ['Ingresos fallidos (24 h)', Audit::where('created_at', '>=', now()->subDay())->where('event', 'login_fallido')->count()],
                ],
                'serie' => $serie,
                'maximo' => max(1, collect($serie)->max('total')),
                'porTipo' => Audit::where('created_at', '>=', now()->subDays(30))->whereNotIn('event', self::ACCESOS)
                    ->selectRaw('event, count(*) as total')->groupBy('event')->orderByDesc('total')->limit(8)->pluck('total', 'event')->all(),
                'masActivos' => $activos->map(fn ($a) => [
                    'nombre' => isset($usuarios[$a->user_id]) ? trim($usuarios[$a->user_id]->nombre.' '.$usuarios[$a->user_id]->apellido) : "Usuario #{$a->user_id}",
                    'total' => (int) $a->getAttribute('total'),
                ])->all(),
            ];
        });
    }

    /** @return array<string, mixed> */
    private function datosAuditoria(bool $soloAccesos): array
    {
        $alcance = fn ($q) => $soloAccesos ? $q->whereIn('event', self::ACCESOS) : $q->whereNotIn('event', self::ACCESOS);

        $consulta = $alcance(Audit::query()->with('user')->latest('id'));

        if ($this->evento !== '') {
            $consulta->where('event', $this->evento);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->desde)) {
            $consulta->where('created_at', '>=', $this->desde.' 00:00:00');
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->hasta)) {
            $consulta->where('created_at', '<=', $this->hasta.' 23:59:59');
        }
        if (trim($this->buscar) !== '') {
            $termino = '%'.addcslashes(trim($this->buscar), '%_\\').'%';
            $ids = User::where(fn ($q) => $q->where('nombre', 'like', $termino)->orWhere('apellido', 'like', $termino)->orWhere('email', 'like', $termino))->limit(200)->pluck('id');
            $consulta->where(fn ($q) => $q->whereIn('user_id', $ids)->orWhere('ip_address', 'like', $termino)->orWhere('new_values', 'like', $termino));
        }

        return [
            'registros' => $consulta->paginate(25),
            'eventos' => $alcance(Audit::query())->distinct()->orderBy('event')->pluck('event'),
            'soloAccesos' => $soloAccesos,
        ];
    }

    /** @return array<string, string> */
    private function sistema(): array
    {
        $conexion = function (string $nombre): string {
            try {
                DB::connection($nombre)->getPdo();

                return 'Conectada';
            } catch (\Throwable) {
                return 'Sin conexión';
            }
        };

        $sinExpediente = function (): string {
            try {
                $n = app(\App\Services\Resoluciones\SinExpediente::class)->buscar()->count();

                return $n === 0 ? 'Ninguna' : $n.' (ver con: php artisan resoluciones:sin-expediente)';
            } catch (\Throwable) {
                return 'No se pudo verificar';
            }
        };

        $libre = @disk_free_space(storage_path());
        $total = @disk_total_space(storage_path());

        return [
            'Aplicación' => config('app.name').' · entorno '.config('app.env').(config('app.debug') ? ' · DEBUG ENCENDIDO' : ''),
            'Laravel / PHP' => app()->version().' / '.PHP_VERSION,
            'Correo (mailer)' => (string) config('mail.default'),
            'Cola' => (string) config('queue.default'),
            'Caché' => (string) config('cache.default'),
            'Sesiones' => (string) config('session.driver'),
            'Base principal' => $conexion(config('database.default')),
            'Base de usuarios' => $conexion('mysql_admin'),
            'Resoluciones con expediente inexistente' => $sinExpediente(),
            'Disco libre' => $libre && $total ? round($libre / 1073741824, 1).' GB de '.round($total / 1073741824, 1).' GB' : 'No disponible',
            'Hora del servidor' => now()->format('d/m/Y H:i:s').' ('.config('app.timezone').')',
        ];
    }
}
