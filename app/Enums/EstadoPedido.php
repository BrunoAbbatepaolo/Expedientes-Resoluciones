<?php

namespace App\Enums;

/** Circuito del pedido de licencia: empleado pide → jefe aprueba → RRHH confirma. */
enum EstadoPedido: string
{
    case PendienteJefe = 'pendiente_jefe';
    case AprobadoJefe = 'aprobado_jefe';
    case RechazadoJefe = 'rechazado_jefe';
    case Confirmado = 'confirmado';
    case RechazadoRrhh = 'rechazado_rrhh';
    case Cancelado = 'cancelado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::PendienteJefe => 'Esperando al jefe',
            self::AprobadoJefe => 'Esperando a RRHH',
            self::RechazadoJefe => 'Rechazado por el jefe',
            self::Confirmado => 'Confirmado',
            self::RechazadoRrhh => 'Rechazado por RRHH',
            self::Cancelado => 'Cancelado',
        };
    }

    public function clases(): string
    {
        return match ($this) {
            self::PendienteJefe, self::AprobadoJefe => 'bg-amber-400/25 text-amber-900 dark:text-amber-200',
            self::Confirmado => 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300',
            self::RechazadoJefe, self::RechazadoRrhh => 'bg-ipv-magenta/15 text-ipv-magenta dark:text-ipv-magenta-light',
            self::Cancelado => 'bg-black/[0.04] text-ipv-ink/60 dark:bg-white/5 dark:text-ipv-ink-dark/60',
        };
    }

    /** Ocupa los días (para superposición y saldo). */
    public function activo(): bool
    {
        return in_array($this, [self::PendienteJefe, self::AprobadoJefe, self::Confirmado], true);
    }

    /** @return list<string> */
    public static function activos(): array
    {
        return [self::PendienteJefe->value, self::AprobadoJefe->value, self::Confirmado->value];
    }
}
