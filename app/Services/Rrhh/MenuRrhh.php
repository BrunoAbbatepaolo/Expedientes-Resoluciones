<?php

namespace App\Services\Rrhh;

use App\Models\RrhhJefatura;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Ítems del grupo RRHH del menú lateral. Se calcula sin consultar el sistema viejo (el
 * menú está en todas las páginas): tener legajo alcanza para mostrar "Mi RRHH"; la
 * pantalla avisa si ese legajo no existe.
 */
class MenuRrhh
{
    /** @return list<array{ruta: string, etiqueta: string}> */
    public static function para(User $user): array
    {
        $esJefe = Cache::remember(self::claveJefe($user->id), now()->addMinutes(10),
            fn () => RrhhJefatura::where('user_id', $user->id)->exists());
        $rrhh = $user->permiso('rrhh_ver');

        $items = [
            ['rrhh.mio', 'Mi RRHH', trim((string) $user->legajo) !== ''],
            ['rrhh.equipo', 'Mi equipo', $esJefe],
            ['rrhh.personal', 'Personal', $rrhh],
            ['rrhh.asistencia', 'Asistencia', $rrhh],
            ['rrhh.licencias', 'Licencias', $rrhh],
            ['rrhh.reportes', 'Reportes', $rrhh],
            ['rrhh.organigrama', 'Organigrama', $rrhh || $user->permiso('lista_usuario_editar')],
            ['rrhh.calendario', 'Calendario', true],
        ];

        return array_values(array_map(
            fn ($i) => ['ruta' => $i[0], 'etiqueta' => $i[1]],
            array_filter($items, fn ($i) => $i[2] && Route::has($i[0])),
        ));
    }

    /** Llamar al cambiar jefaturas para que el menú se actualice enseguida. */
    public static function olvidarJefe(int $userId): void
    {
        Cache::forget(self::claveJefe($userId));
    }

    private static function claveJefe(int $userId): string
    {
        return "rrhh.menu.jefe.$userId";
    }
}
