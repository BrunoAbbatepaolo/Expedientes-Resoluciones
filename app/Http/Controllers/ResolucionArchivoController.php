<?php

namespace App\Http\Controllers;

use App\Models\Resolucion;
use App\Models\ResolucionArchivo;
use App\Traits\AuthorizesOficina;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sirve los PDFs de resoluciones desde el disco privado, previa autorización.
 * Los archivos tienen DNI y datos personales: no deben quedar en el disco público.
 */
class ResolucionArchivoController extends Controller
{
    use AuthorizesOficina;

    /**
     * Vista previa de un PDF recién subido (todavía sin resolución guardada).
     * Solo accede a los archivos de la sesión del propio usuario.
     */
    public function temporal(int $index): Response
    {
        $this->autorizarPermiso('resolucion_editar');

        $ruta = session('archivos_pdf_resolucion', [])[$index] ?? null;

        return $this->servir($ruta, 'vista-previa.pdf');
    }

    public function archivo(ResolucionArchivo $archivo): Response
    {
        $this->autorizarResolucion($archivo->resolucion()->firstOrFail(), 'resolucion_ver');

        return $this->servir($archivo->ruta, $archivo->nombre_original);
    }

    /**
     * PDF cargado desde "Editar" (columna resoluciones.pdf).
     */
    public function pdf(Resolucion $resolucion): Response
    {
        $this->autorizarResolucion($resolucion, 'resolucion_ver');

        return $this->servir($resolucion->pdf, 'resolucion.pdf');
    }

    private function servir(?string $ruta, string $nombre): Response
    {
        abort_if(blank($ruta), 404);

        // Los archivos anteriores a este cambio quedaron en el disco público.
        foreach (['local', 'public'] as $disco) {
            if (Storage::disk($disco)->exists($ruta)) {
                return Storage::disk($disco)->response($ruta, $nombre, [
                    'Content-Disposition' => 'inline; filename="'.addslashes($nombre).'"',
                ]);
            }
        }

        abort(404, 'Archivo no encontrado');
    }
}
