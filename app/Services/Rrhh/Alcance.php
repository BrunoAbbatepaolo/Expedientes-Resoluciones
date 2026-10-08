<?php

namespace App\Services\Rrhh;

use App\Enums\NivelDatoRrhh;

/**
 * Qué puede ver un usuario en RRHH (ver docs/spec-modulo-rrhh.md, sección 5):
 * lo propio (por legajo), su equipo (jefaturas + oficinas dependientes) o todo (rrhh_ver).
 */
final readonly class Alcance
{
    /**
     * @param  list<int>  $oficinasEquipo  oficinas RRHH de sus jefaturas y todas sus dependientes
     * @param  list<int>  $oficinasJefatura  oficinas RRHH asignadas directamente como jefe
     */
    public function __construct(
        public int $userId,
        public ?int $empleadoId,
        public bool $rrhh,
        public bool $sensibles,
        public array $oficinasEquipo = [],
        public array $oficinasJefatura = [],
        public bool $horarios = false,
    ) {}

    public function esJefe(): bool
    {
        return $this->oficinasEquipo !== [];
    }

    public function puedeEntrar(): bool
    {
        return $this->empleadoId !== null || $this->rrhh || $this->esJefe();
    }

    /** Oficinas RRHH cuyos empleados puede listar; null = todas. @return list<int>|null */
    public function oficinasVisibles(): ?array
    {
        return $this->rrhh ? null : $this->oficinasEquipo;
    }

    public function veOficina(int $oficinaId): bool
    {
        return $this->rrhh || in_array($oficinaId, $this->oficinasEquipo, true);
    }

    /** Nivel de datos del legajo que puede ver de un empleado; null = ninguno. */
    public function nivelPara(int $empleadoId, int $oficinaId): ?NivelDatoRrhh
    {
        return match (true) {
            $empleadoId === $this->empleadoId => NivelDatoRrhh::Sensible,
            $this->rrhh => $this->sensibles ? NivelDatoRrhh::Sensible : NivelDatoRrhh::Personal,
            in_array($oficinaId, $this->oficinasEquipo, true) => NivelDatoRrhh::Basico,
            default => null,
        };
    }

    /** La observación de una licencia puede tener datos médicos: el jefe no la ve. */
    public function veObservacion(int $empleadoId): bool
    {
        return $empleadoId === $this->empleadoId || $this->rrhh;
    }
}
