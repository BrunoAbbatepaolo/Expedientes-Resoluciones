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

    /** Trazo (path SVG, viewBox 24) del ícono del chip. */
    public function icono(): string
    {
        return match ($this) {
            self::PendienteJefe => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            self::AprobadoJefe => 'M6 2h12M6 22h12M7 2v4a5 5 0 002 4 5 5 0 00-2 4v4M17 2v4a5 5 0 01-2 4 5 5 0 012 4v4',
            self::Confirmado => 'M5 13l4 4L19 7',
            self::RechazadoJefe, self::RechazadoRrhh => 'M6 18L18 6M6 6l12 12',
            self::Cancelado => 'M18.364 5.636A9 9 0 115.636 18.364 9 9 0 0118.364 5.636zM5.636 5.636l12.728 12.728',
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
