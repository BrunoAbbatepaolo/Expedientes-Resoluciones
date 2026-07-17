<?php

namespace App\Traits;

use App\Models\Expediente;
use App\Models\Resolucion;

trait AuthorizesOficina
{
    protected function autorizarPermiso(string $permiso): void
    {
        abort_unless((bool) auth()->user()?->permiso($permiso), 403);
    }

    protected function autorizarExpediente(Expediente $expediente, string $permiso): void
    {
        $this->autorizarPermiso($permiso);

        $oficinaId = auth()->user()->oficinaAsignadaId()
            ?? auth()->user()->oficinaIdPara($permiso);

        abort_unless($oficinaId && (int) $expediente->oficina_id === (int) $oficinaId, 403);
    }

    protected function autorizarResolucion(Resolucion $resolucion, string $permiso): void
    {
        $this->autorizarPermiso($permiso);

        // Resoluciones viejas sin oficina asignada (ver implementacion_futuro.md 2.1):
        // no se bloquean todavía, solo se protegen las que ya tienen oficina_id.
        if ($resolucion->oficina_id === null) {
            return;
        }

        $oficinaId = auth()->user()->oficinaAsignadaId()
            ?? auth()->user()->oficinaIdPara($permiso);

        abort_unless($oficinaId && (int) $resolucion->oficina_id === (int) $oficinaId, 403);
    }
}
