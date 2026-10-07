<?php

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Services\Rrhh\AlcanceFactory;
use App\Services\Rrhh\Contracts\Legajos;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Descarga de un archivo adjunto al legajo (nivel personal o superior). */
class ArchivoEmpleadoController extends Controller
{
    public function __invoke(Request $request, int $empleado, int $archivo, Legajos $legajos, AlcanceFactory $alcances): Response
    {
        $adjunto = $legajos->archivo($alcances->para($request->user()), $empleado, $archivo);
        abort_if($adjunto === null, 404);

        // En PostgreSQL el bytea llega como stream.
        $contenido = is_resource($adjunto->contenido) ? stream_get_contents($adjunto->contenido) : (string) $adjunto->contenido;

        return response($contenido, 200, [
            'Content-Type' => $adjunto->mimetype ?: 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.addcslashes(basename((string) $adjunto->nombre), '"\\').'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
