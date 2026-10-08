<?php

namespace App\Services\Rrhh;

use App\Enums\EstadoPedido;
use App\Models\Mitiv\Empleado;
use App\Models\RrhhJefatura;
use App\Models\RrhhPedidoLicencia;
use App\Models\RrhhTipoPedible;
use App\Models\User;
use App\Services\Rrhh\Contracts\Calendario;
use App\Services\Rrhh\Contracts\Licencias;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Circuito del pedido de licencia (spec 7 bis): el empleado pide desde Mi RRHH, lo aprueba
 * un jefe de su oficina (o de una superior) y lo confirma RRHH. Vive en tablas de SiRex: un
 * pedido no es una licencia hasta que RRHH lo carga, así que no choca con el sistema viejo.
 */
class PedidosLicencia
{
    public function __construct(
        private Organigrama $organigrama,
        private Calendario $calendario,
        private Licencias $licencias,
    ) {}

    public static function habilitado(): bool
    {
        return (bool) config('sirex.rrhh_pedidos');
    }

    /** @return Collection<int, RrhhTipoPedible> */
    public function tiposPedibles(): Collection
    {
        return RrhhTipoPedible::orderBy('nombre')->get();
    }

    /**
     * Vista previa para el formulario: días hábiles y, si es de vacaciones, cómo queda el saldo.
     *
     * @return array{dias: int, vacaciones: bool, pendientes: int|null, despues: int|null}
     */
    public function previsualizar(Alcance $alcance, ?int $tipoAusenciaId, ?string $desde, ?string $hasta): array
    {
        $vacio = ['dias' => 0, 'vacaciones' => false, 'pendientes' => null, 'despues' => null];
        if ($alcance->empleadoId === null || ! $tipoAusenciaId || ! $this->fecha($desde) || ! $this->fecha($hasta) || $hasta < $desde) {
            return $vacio;
        }

        $dias = $this->diasHabiles(CarbonImmutable::parse($desde), CarbonImmutable::parse($hasta));
        if (! $this->licencias->esTipoVacaciones($tipoAusenciaId)) {
            return ['dias' => $dias] + $vacio;
        }

        $disponibles = $this->vacacionesDisponibles($alcance);

        return ['dias' => $dias, 'vacaciones' => true, 'pendientes' => $disponibles, 'despues' => $disponibles - $dias];
    }

    /** @param  array<string, mixed>  $datos */
    public function crear(Alcance $alcance, array $datos, ?UploadedFile $adjunto = null): RrhhPedidoLicencia
    {
        $this->exigirHabilitado();
        if ($alcance->empleadoId === null) {
            throw new AuthorizationException('Tu usuario no tiene un legajo del sistema de personal.');
        }

        $tipos = $this->tiposPedibles()->keyBy('tipo_ausencia_id');
        $v = Validator::make($datos + ['adjunto' => $adjunto], [
            'tipo_ausencia_id' => ['required', 'integer', fn ($a, $valor, $fallar) => $tipos->has((int) $valor) ?: $fallar('Ese tipo de licencia no se puede pedir desde acá.')],
            'desde' => ['required', 'date_format:Y-m-d'],
            'hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'observacion' => ['nullable', 'string', 'max:500'],
            'adjunto' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ], [], ['tipo_ausencia_id' => 'tipo de licencia', 'hasta' => 'hasta'])->validate();

        $tipo = $tipos[(int) $v['tipo_ausencia_id']];
        $desde = CarbonImmutable::parse($v['desde']);
        $hasta = CarbonImmutable::parse($v['hasta']);
        $hoy = Hora::hoy();
        $errores = [];

        if ($tipo->requiere_adjunto && $adjunto === null) {
            $errores['adjunto'] = 'Este tipo de licencia requiere adjuntar un certificado.';
        }
        if (! $tipo->permite_retroactivo && $desde->lt($hoy)) {
            $errores['desde'] = 'No se puede pedir con fecha pasada.';
        } elseif ($desde->lt($hoy->subDays((int) config('sirex.rrhh_pedido_dias_retro')))) {
            $errores['desde'] = 'Solo se puede pedir hasta '.config('sirex.rrhh_pedido_dias_retro').' días hacia atrás.';
        }

        $dias = $this->diasHabiles($desde, $hasta);
        if ($dias === 0) {
            $errores['hasta'] = 'El período elegido no tiene días hábiles.';
        }
        if ($this->licencias->tieneLicenciaEntre($alcance->empleadoId, $desde, $hasta) || $this->pedidosActivos($alcance->empleadoId)->contains(
            fn (RrhhPedidoLicencia $p) => $p->desde->toDateString() <= $hasta->toDateString() && $p->hasta->toDateString() >= $desde->toDateString()
        )) {
            $errores['desde'] = 'Se superpone con otra licencia o con un pedido tuyo en trámite.';
        }

        $esVacaciones = $this->licencias->esTipoVacaciones($tipo->tipo_ausencia_id);
        if ($esVacaciones && $dias > ($disponibles = $this->vacacionesDisponibles($alcance))) {
            $errores['hasta'] = "Pedís {$dias} días hábiles y te quedan {$disponibles} de vacaciones.";
        }

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }

        $oficinaId = (int) Empleado::whereKey($alcance->empleadoId)->value('oficina_id');
        $conJefe = $this->jefesDe($oficinaId, exceptoUser: $alcance->userId) !== [];

        $pedido = RrhhPedidoLicencia::create([
            'empleado_id' => $alcance->empleadoId,
            'oficina_id' => $oficinaId,
            'user_id' => $alcance->userId,
            'tipo_ausencia_id' => $tipo->tipo_ausencia_id,
            'tipo_nombre' => $tipo->nombre,
            'es_vacaciones' => $esVacaciones,
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'dias_habiles' => $dias,
            'observacion' => $v['observacion'] ?? null,
            'adjunto_ruta' => $adjunto?->store('rrhh/pedidos', 'local'),
            'adjunto_nombre' => $adjunto?->getClientOriginalName(),
            'estado' => $conJefe ? EstadoPedido::PendienteJefe : EstadoPedido::AprobadoJefe,
            'jefe_motivo' => $conJefe ? null : 'Sin jefe asignado: pasa directo a RRHH',
        ]);
        $this->olvidarContadores();

        return $pedido;
    }

    public function cancelar(Alcance $alcance, int $pedidoId): void
    {
        $this->exigirHabilitado();
        $pedido = RrhhPedidoLicencia::findOrFail($pedidoId);
        if ($pedido->user_id !== $alcance->userId) {
            throw new AuthorizationException('Solo podés cancelar tus pedidos.');
        }

        $this->transicion($pedido, EstadoPedido::PendienteJefe, ['estado' => EstadoPedido::Cancelado]);
    }

    /** @return Collection<int, RrhhPedidoLicencia> */
    public function mios(Alcance $alcance): Collection
    {
        return RrhhPedidoLicencia::with(['jefe:id,nombre,apellido', 'rrhh:id,nombre,apellido'])
            ->where('user_id', $alcance->userId)
            ->latest()
            ->limit(30)
            ->get();
    }

    /** @return Collection<int, RrhhPedidoLicencia> */
    public function porAprobar(Alcance $alcance): Collection
    {
        if (! $alcance->esJefe()) {
            return collect();
        }

        return RrhhPedidoLicencia::with('user:id,nombre,apellido')
            ->where('estado', EstadoPedido::PendienteJefe->value)
            ->whereIn('oficina_id', $alcance->oficinasEquipo)
            ->where('user_id', '!=', $alcance->userId)
            ->orderBy('desde')
            ->get();
    }

    public function aprobar(Alcance $alcance, int $pedidoId): void
    {
        $pedido = $this->pedidoDelEquipo($alcance, $pedidoId);
        $this->transicion($pedido, EstadoPedido::PendienteJefe, [
            'estado' => EstadoPedido::AprobadoJefe, 'jefe_id' => $alcance->userId, 'jefe_at' => now(),
        ]);
    }

    public function rechazarJefe(Alcance $alcance, int $pedidoId, string $motivo): void
    {
        $pedido = $this->pedidoDelEquipo($alcance, $pedidoId);
        $this->transicion($pedido, EstadoPedido::PendienteJefe, [
            'estado' => EstadoPedido::RechazadoJefe, 'jefe_id' => $alcance->userId, 'jefe_at' => now(), 'jefe_motivo' => $this->motivo($motivo),
        ]);
    }

    /** @return Collection<int, RrhhPedidoLicencia> */
    public function porConfirmar(Alcance $alcance): Collection
    {
        $this->exigirConfirmador($alcance);

        return RrhhPedidoLicencia::with(['user:id,nombre,apellido', 'jefe:id,nombre,apellido'])
            ->where('estado', EstadoPedido::AprobadoJefe->value)
            ->orderBy('desde')
            ->get();
    }

    /** @return Collection<int, RrhhPedidoLicencia> */
    public function historial(Alcance $alcance): Collection
    {
        $this->exigirConfirmador($alcance);

        return RrhhPedidoLicencia::with(['user:id,nombre,apellido', 'jefe:id,nombre,apellido', 'rrhh:id,nombre,apellido'])
            ->whereNotIn('estado', [EstadoPedido::PendienteJefe->value, EstadoPedido::AprobadoJefe->value])
            ->latest('updated_at')
            ->limit(50)
            ->get();
    }

    public function confirmar(Alcance $alcance, int $pedidoId): void
    {
        $this->exigirConfirmador($alcance);
        $this->transicion(RrhhPedidoLicencia::findOrFail($pedidoId), EstadoPedido::AprobadoJefe, [
            'estado' => EstadoPedido::Confirmado, 'rrhh_id' => $alcance->userId, 'rrhh_at' => now(),
        ]);
    }

    public function rechazarRrhh(Alcance $alcance, int $pedidoId, string $motivo): void
    {
        $this->exigirConfirmador($alcance);
        $this->transicion(RrhhPedidoLicencia::findOrFail($pedidoId), EstadoPedido::AprobadoJefe, [
            'estado' => EstadoPedido::RechazadoRrhh, 'rrhh_id' => $alcance->userId, 'rrhh_at' => now(), 'rrhh_motivo' => $this->motivo($motivo),
        ]);
    }

    /** Pedido con adjunto que el usuario puede ver (el propio, sus jefes o RRHH). */
    public function conAdjunto(Alcance $alcance, int $pedidoId): RrhhPedidoLicencia
    {
        $pedido = RrhhPedidoLicencia::findOrFail($pedidoId);
        $puede = $pedido->user_id === $alcance->userId || $alcance->rrhh || $alcance->confirmaPedidos
            || in_array($pedido->oficina_id, $alcance->oficinasEquipo, true);
        if (! $puede) {
            throw new AuthorizationException('No podés ver este adjunto.');
        }

        return $pedido;
    }

    public function guardarTipo(Alcance $alcance, int $tipoAusenciaId, bool $retroactivo, bool $adjunto): void
    {
        $this->exigirConfirmador($alcance);
        $nombre = $this->licencias->tipos()->get($tipoAusenciaId);
        if ($nombre === null) {
            throw ValidationException::withMessages(['tipo' => 'Ese tipo de licencia no existe.']);
        }

        RrhhTipoPedible::updateOrCreate(
            ['tipo_ausencia_id' => $tipoAusenciaId],
            ['nombre' => $nombre, 'permite_retroactivo' => $retroactivo, 'requiere_adjunto' => $adjunto],
        );
    }

    public function quitarTipo(Alcance $alcance, int $tipoPedibleId): void
    {
        $this->exigirConfirmador($alcance);
        RrhhTipoPedible::whereKey($tipoPedibleId)->delete();
    }

    /**
     * Pedidos esperando a este usuario, para el menú. Sin consultar el sistema viejo.
     *
     * @return array{aprobar: int, confirmar: int}
     */
    public static function contadores(User $user): array
    {
        if (! self::habilitado()) {
            return ['aprobar' => 0, 'confirmar' => 0];
        }

        return Cache::remember("rrhh.pedidos.contadores.{$user->id}", now()->addMinute(), function () use ($user) {
            $jefaturas = RrhhJefatura::where('user_id', $user->id)->pluck('mitiv_oficina_id')->map(fn ($id) => (int) $id)->all();
            $oficinas = $jefaturas === [] ? [] : app(Organigrama::class)->descendientes($jefaturas);

            return [
                'aprobar' => $oficinas === [] ? 0 : RrhhPedidoLicencia::where('estado', EstadoPedido::PendienteJefe->value)
                    ->whereIn('oficina_id', $oficinas)->where('user_id', '!=', $user->id)->count(),
                'confirmar' => $user->permiso('rrhh_confirmar_pedidos')
                    ? RrhhPedidoLicencia::where('estado', EstadoPedido::AprobadoJefe->value)->count() : 0,
            ];
        });
    }

    /** Días de vacaciones del período vigente que quedan libres, descontando pedidos en trámite. */
    private function vacacionesDisponibles(Alcance $alcance): int
    {
        $v = $this->licencias->vacaciones($alcance, (int) $alcance->empleadoId);
        $enTramite = $this->pedidosActivos((int) $alcance->empleadoId)
            ->filter(fn (RrhhPedidoLicencia $p) => $p->es_vacaciones && $p->estado !== EstadoPedido::Confirmado)
            ->sum('dias_habiles');

        return $v->pendientes - (int) $enTramite;
    }

    /** @return Collection<int, RrhhPedidoLicencia> */
    private function pedidosActivos(int $empleadoId): Collection
    {
        return RrhhPedidoLicencia::where('empleado_id', $empleadoId)->whereIn('estado', EstadoPedido::activos())->get();
    }

    private function diasHabiles(CarbonImmutable $desde, CarbonImmutable $hasta): int
    {
        return CalculoVacaciones::diasHabiles($desde, $hasta, $this->calendario->diasNoHabiles($desde, $hasta));
    }

    /** Usuarios jefes de la oficina (por jefatura directa o de una oficina superior). @return list<int> */
    private function jefesDe(int $oficinaId, int $exceptoUser): array
    {
        return RrhhJefatura::where('user_id', '!=', $exceptoUser)->get()
            ->filter(fn (RrhhJefatura $j) => in_array($oficinaId, $this->organigrama->descendientes([(int) $j->mitiv_oficina_id]), true))
            ->pluck('user_id')->unique()->values()->all();
    }

    private function pedidoDelEquipo(Alcance $alcance, int $pedidoId): RrhhPedidoLicencia
    {
        $this->exigirHabilitado();
        $pedido = RrhhPedidoLicencia::findOrFail($pedidoId);
        if (! in_array($pedido->oficina_id, $alcance->oficinasEquipo, true) || $pedido->user_id === $alcance->userId) {
            throw new AuthorizationException('Este pedido no es de tu equipo.');
        }

        return $pedido;
    }

    /** Cambia de estado solo si sigue en el esperado (evita que dos personas lo procesen a la vez). */
    private function transicion(RrhhPedidoLicencia $pedido, EstadoPedido $desde, array $cambios): void
    {
        $cambios['estado'] = $cambios['estado']->value;
        $filas = RrhhPedidoLicencia::whereKey($pedido->id)->where('estado', $desde->value)->update($cambios + ['updated_at' => now()]);
        if ($filas === 0) {
            throw ValidationException::withMessages(['pedido' => 'Este pedido ya fue procesado por otra persona.']);
        }

        $pedido->refresh();
        $pedido->recordAudit('updated', ['estado' => $desde->value], $cambios);
        $this->olvidarContadores();
    }

    private function motivo(string $motivo): string
    {
        $motivo = trim($motivo);
        if ($motivo === '') {
            throw ValidationException::withMessages(['motivo' => 'Indicá el motivo del rechazo.']);
        }

        return mb_substr($motivo, 0, 500);
    }

    private function exigirHabilitado(): void
    {
        abort_unless(self::habilitado(), 404);
    }

    private function exigirConfirmador(Alcance $alcance): void
    {
        $this->exigirHabilitado();
        if (! $alcance->confirmaPedidos) {
            throw new AuthorizationException('Necesitás el permiso para confirmar pedidos de licencia.');
        }
    }

    private function fecha(?string $valor): bool
    {
        return $valor !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) === 1;
    }

    private function olvidarContadores(): void
    {
        // Pocos usuarios: se invalidan todos los contadores en lugar de calcular a quiénes afecta.
        foreach (User::pluck('id') as $id) {
            Cache::forget("rrhh.pedidos.contadores.$id");
        }
    }
}
