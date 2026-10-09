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

    /** Trazo (path SVG, viewBox 24) del ícono del chip; vacío si no lleva. */
    public function icono(): string
    {
        return match ($this) {
            self::Presente => 'M5 13l4 4L19 7',
            self::Tarde => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            self::Incompleto => 'M12 3a9 9 0 100 18V3z',
            self::Falta => 'M6 18L18 6M6 6l12 12',
            self::Justificado => 'M9 12l2 2 4-4M12 3l8 3v6c0 4.5-3.2 8.2-8 9-4.8-.8-8-4.5-8-9V6l8-3z',
            self::Licencia => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            self::Feriado => 'M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z',
            self::Franco => 'M20 12.8A8 8 0 1111.2 4 6.5 6.5 0 0020 12.8z',
            self::Pendiente => 'M6 2h12M6 22h12M7 2v4a5 5 0 002 4 5 5 0 00-2 4v4M17 2v4a5 5 0 01-2 4 5 5 0 012 4v4',
            self::Futuro => '',
        };
    }

    /** Cuenta como "no vino" en totales de tableros. */
    public function esAusencia(): bool
    {
        return $this === self::Falta;
    }
}
