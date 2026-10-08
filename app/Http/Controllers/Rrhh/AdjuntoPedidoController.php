<?php

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Services\Rrhh\AlcanceFactory;
use App\Services\Rrhh\PedidosLicencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/** Certificado adjunto a un pedido de licencia: lo ve el empleado, sus jefes y RRHH. */
class AdjuntoPedidoController extends Controller
{
    public function __invoke(Request $request, int $pedido, PedidosLicencia $pedidos, AlcanceFactory $alcances): Response
    {
        abort_unless(PedidosLicencia::habilitado(), 404);

        $registro = $pedidos->conAdjunto($alcances->para($request->user()), $pedido);
        abort_if($registro->adjunto_ruta === null || ! Storage::disk('local')->exists($registro->adjunto_ruta), 404);

        return Storage::disk('local')->response($registro->adjunto_ruta, basename((string) $registro->adjunto_nombre), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
