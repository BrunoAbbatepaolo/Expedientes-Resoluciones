<?php

namespace App\Services\Rrhh;

use App\Models\Mitiv\Empleado;
use App\Models\RrhhJefatura;
use App\Models\User;

/**
 * Arma el Alcance de un usuario una sola vez por request (registrado como scoped).
 */
class AlcanceFactory
{
    /** @var array<int, Alcance> */
    private array $memo = [];

    public function __construct(private Organigrama $organigrama) {}

    public function para(User $user): Alcance
    {
        return $this->memo[$user->id] ??= $this->construir($user);
    }

    private function construir(User $user): Alcance
    {
        $jefaturas = RrhhJefatura::where('user_id', $user->id)
            ->pluck('mitiv_oficina_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return new Alcance(
            userId: $user->id,
            empleadoId: $this->empleadoDe($user),
            rrhh: $user->permiso('rrhh_ver'),
            sensibles: $user->permiso('rrhh_sensibles'),
            oficinasEquipo: $jefaturas !== [] ? $this->organigrama->descendientes($jefaturas) : [],
            oficinasJefatura: $jefaturas,
        );
    }

    /** Empleado del sistema viejo con el legajo del usuario (si mitivdb no responde, ninguno). */
    private function empleadoDe(User $user): ?int
    {
        $legajo = trim((string) $user->legajo);
        if ($legajo === '' || ! ctype_digit($legajo)) {
            return null;
        }

        $id = rescue(fn () => Empleado::where('legajo', (int) $legajo)
            ->orderByDesc('habilitado')
            ->value('id'), null, report: false);

        return $id !== null ? (int) $id : null;
    }
}
