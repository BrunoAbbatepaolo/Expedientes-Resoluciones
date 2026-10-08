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
    /** @return list<array{ruta: string, etiqueta: string, contador: int}> */
    public static function para(User $user): array
    {
        $esJefe = Cache::remember(self::claveJefe($user->id), now()->addMinutes(10),
            fn () => RrhhJefatura::where('user_id', $user->id)->exists());
        $rrhh = $user->permiso('rrhh_ver');
        $pedidos = PedidosLicencia::contadores($user);

        $items = [
            ['rrhh.mio', 'Mi RRHH', trim((string) $user->legajo) !== '', 0],
            ['rrhh.equipo', 'Mi equipo', $esJefe, $pedidos['aprobar']],
            ['rrhh.personal', 'Personal', $rrhh, 0],
            ['rrhh.asistencia', 'Asistencia', $rrhh, 0],
            ['rrhh.licencias', 'Licencias', $rrhh, $pedidos['confirmar']],
            ['rrhh.reportes', 'Reportes', $rrhh, 0],
            ['rrhh.organigrama', 'Organigrama', $rrhh || $user->permiso('lista_usuario_editar'), 0],
            ['rrhh.calendario', 'Calendario', true, 0],
        ];

        return array_values(array_map(
            fn ($i) => ['ruta' => $i[0], 'etiqueta' => $i[1], 'contador' => $i[3]],
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
