<?php

namespace App\Enums;

/** Estado de un día de asistencia de un empleado (ver App\Services\Rrhh\CalculoAsistencia). */
enum EstadoDia: string
{
    case Presente = 'presente';
    case Tarde = 'tarde';
    case Incompleto = 'incompleto';
    case Falta = 'falta';
    case Justificado = 'justificado';
    case Licencia = 'licencia';
    case Feriado = 'feriado';
    case Franco = 'franco';
    case Pendiente = 'pendiente';
    case Futuro = 'futuro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Presente => 'Presente',
            self::Tarde => 'Tarde',
            self::Incompleto => 'Incompleto',
            self::Falta => 'Falta',
            self::Justificado => 'Justificado',
            self::Licencia => 'Licencia',
            self::Feriado => 'Feriado',
            self::Franco => 'Franco',
            self::Pendiente => 'Pendiente',
            self::Futuro => '',
        };
    }

    /** Clases Tailwind del chip/celda (claro y oscuro). */
    public function clases(): string
    {
        return match ($this) {
            self::Presente => 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300',
            self::Tarde => 'bg-amber-400/25 text-amber-900 dark:text-amber-200',
            self::Incompleto => 'bg-orange-400/20 text-orange-900 dark:text-orange-200',
            self::Falta => 'bg-ipv-magenta/15 text-ipv-magenta dark:text-ipv-magenta-light',
            self::Justificado => 'bg-sky-500/15 text-sky-800 dark:text-sky-300',
            self::Licencia => 'bg-ipv-blue/15 text-ipv-blue dark:text-sky-300',
            self::Feriado => 'bg-violet-500/15 text-violet-800 dark:text-violet-300',
            self::Franco => 'bg-black/[0.04] text-ipv-ink/50 dark:bg-white/5 dark:text-ipv-ink-dark/50',
            self::Pendiente => 'bg-black/[0.04] text-ipv-ink/60 dark:bg-white/5 dark:text-ipv-ink-dark/60',
            self::Futuro => 'text-ipv-ink/30 dark:text-ipv-ink-dark/30',
        };
    }

    /** Cuenta como "no vino" en totales de tableros. */
    public function esAusencia(): bool
    {
        return $this === self::Falta;
    }
}
