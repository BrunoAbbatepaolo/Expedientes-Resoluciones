<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Horario especial de un empleado (lo carga la jefatura de RRHH). Reemplaza al del
 * sistema viejo en los días indicados mientras está vigente.
 *
 * @property int $id
 * @property int $empleado_id
 * @property list<int> $dias 1 = lunes … 7 = domingo
 * @property string $desde_maniana
 * @property string $hasta_maniana
 * @property string|null $desde_tarde
 * @property string|null $hasta_tarde
 * @property int|null $tolerancia
 * @property Carbon $vigente_desde
 * @property Carbon|null $vigente_hasta
 * @property string $motivo
 * @property int $user_id
 * @property-read User|null $user
 */
class RrhhHorarioEspecial extends Model
{
    use Auditable;

    protected $connection = 'mysql_admin';

    protected $table = 'rrhh_horarios_especiales';

    protected $fillable = [
        'empleado_id', 'dias', 'desde_maniana', 'hasta_maniana', 'desde_tarde', 'hasta_tarde',
        'tolerancia', 'vigente_desde', 'vigente_hasta', 'motivo', 'user_id',
    ];

    protected function casts(): array
    {
        return ['dias' => 'array', 'vigente_desde' => 'date', 'vigente_hasta' => 'date'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Vigentes en algún día del rango [desde, hasta]. */
    public function scopeEntre(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereDate('vigente_desde', '<=', $hasta)
            ->where(fn ($q) => $q->whereNull('vigente_hasta')->orWhereDate('vigente_hasta', '>=', $desde));
    }

    public function aplicaEn(CarbonImmutable $fecha): bool
    {
        return in_array($fecha->dayOfWeekIso, array_map('intval', $this->dias), true)
            && $this->vigente_desde->toDateString() <= $fecha->toDateString()
            && ($this->vigente_hasta === null || $this->vigente_hasta->toDateString() >= $fecha->toDateString());
    }

    /** Con la forma de una fila de mitivdb.horarios, para CalculoAsistencia. */
    public function comoHorario(): object
    {
        return (object) [
            'desde_maniana' => $this->desde_maniana,
            'hasta_maniana' => $this->hasta_maniana,
            'tolerancia_maniana' => $this->tolerancia,
            'desde_tarde' => $this->desde_tarde,
            'hasta_tarde' => $this->hasta_tarde,
            'tolerancia_tarde' => $this->tolerancia,
            'especial' => true,
        ];
    }

    public function estado(CarbonImmutable $hoy): string
    {
        return match (true) {
            $this->vigente_desde->toDateString() > $hoy->toDateString() => 'próximo',
            $this->vigente_hasta !== null && $this->vigente_hasta->toDateString() < $hoy->toDateString() => 'vencido',
            default => 'vigente',
        };
    }
}
