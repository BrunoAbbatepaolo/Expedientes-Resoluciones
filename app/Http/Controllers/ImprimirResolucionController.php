<?php

namespace App\Http\Controllers;

use App\Models\Resolucion;
use App\Services\Resoluciones\Documento;
use App\Traits\AuthorizesOficina;
use Illuminate\Contracts\View\View;
use Mews\Purifier\Facades\Purifier;

/**
 * Resolución lista para imprimir o "Guardar como PDF". Las de "Plantilla completa" se vuelven a armar desde
 * su tipo y sus datos (con el diseño del prototipo); las demás muestran el HTML guardado (ya saneado).
 */
class ImprimirResolucionController extends Controller
{
    use AuthorizesOficina;

    public function __invoke(Resolucion $resolucion): View
    {
        $this->autorizarResolucion($resolucion, 'resolucion_ver');

        /** @var array{campos?: array<string, mixed>, articulos_quitados?: list<int>}|null $datos cast 'array' del modelo */
        $datos = $resolucion->datos;
        $conPrototipo = $resolucion->tipo && is_array($datos) && Documento::existe($resolucion->tipo);

        $html = $conPrototipo
            ? Documento::html($resolucion->tipo, (array) ($datos['campos'] ?? []), array_map('intval', $datos['articulos_quitados'] ?? []))
            : Purifier::clean((string) $resolucion->plantilla);

        return view('resoluciones.imprimir', [
            'resolucion' => $resolucion,
            'html' => $html,
            'conPrototipo' => $conPrototipo,
        ]);
    }
}
