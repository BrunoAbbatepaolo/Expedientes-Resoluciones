<?php

namespace App\Models;

use App\Models\Mitiv\MitivModel;

/**
 * Vista de mitivdb (sistema viejo) usada al cargar un expediente en "Mi oficina".
 * Solo lectura: hereda el bloqueo de escritura de MitivModel.
 */
class VistaExpedientes extends MitivModel
{
    protected $table = 'vista_expedientes';
}
