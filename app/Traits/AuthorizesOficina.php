<?php

namespace App\Traits;

use App\Models\Expediente;

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
}
