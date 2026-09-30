<?php

namespace App\Http\Controllers;

use App\Services\ExpedientesIpv;
use App\Traits\AuthorizesOficina;
use Illuminate\Contracts\View\View;

/**
 * Carátula + historial de pases de un expediente del IPV, listos para imprimir o
 * guardar como PDF desde el navegador (reemplaza los PDF del sistema viejo).
 */
class ImprimirExpedienteIpvController extends Controller
{
    use AuthorizesOficina;

    public function __invoke(int $id, ExpedientesIpv $ipv): View
    {
        $this->autorizarPermiso('expediente_ver');

        $ficha = $ipv->ficha($id);
        abort_if($ficha === null, 404);

        return view('expedientes.imprimir-ipv', ['ficha' => $ficha]);
    }
}
