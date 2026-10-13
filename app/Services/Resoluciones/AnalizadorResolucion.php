<?php

namespace App\Services\Resoluciones;

use Illuminate\Support\Str;

/**
 * Detecta de qué tipo es una resolución a partir de su texto y rescata los datos que
 * pueda. Es una SUGERENCIA para precargar el formulario: la persona la revisa.
 * Las frases clave salen de la redacción de resources/views/prototipos/*.
 */
class AnalizadorResolucion
{
    /** Frases (en minúscula y sin tildes) que delatan cada tipo, con su peso. */
    private const FIRMAS = [
        'Transferencia-Cancelacion' => ['transferencia y aprobacion de cancelacion' => 10, 'transferencia y cancelacion' => 10, 'aprobando la transferencia y la cancelacion' => 10],
        'Transferencias' => ['autorizacion de transferencia' => 4, 'boleto de venta' => 2, 'aprobar la transferencia' => 3, 'plan de readecuacion de pagos' => 2],
        'Cancelaciones' => ['cancelacion definitiva y levantamiento de hipoteca' => 5, 'levantamiento de hipoteca' => 3],
        'Resciciones' => ['sin efecto el acta de tenencia' => 4, 'falta de pago' => 2, 'desalojo' => 2, 'revocar la resolucion de adjudicacion' => 3],
        'Rectificacion' => ['rectificar' => 3, 'donde dice' => 4, 'rectificacion de resolucion' => 3],
        'AplicarPagos' => ['aplicar pago' => 5, 'pago especial' => 3],
        'ReconocimientoCuotaPagadaDosVeces' => ['pagada dos veces' => 5, 'pagadas doblemente' => 4, 'pagada doblemente' => 4],
        'ReconocimientoCuotaPagadaNoCargada' => ['pagada y no cargada' => 5, 'pagadas y no cargadas' => 5],
    ];

    private const MESES = [
        'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4, 'mayo' => 5, 'junio' => 6,
        'julio' => 7, 'agosto' => 8, 'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12,
    ];

    /**
     * @return array{tipo: ?string, datos: array<string, string>}
     */
    public function analizar(string $texto): array
    {
        $plano = trim(preg_replace('/\s+/u', ' ', $texto));
        $tipo = $this->detectarTipo($plano);

        return ['tipo' => $tipo, 'datos' => $tipo ? $this->extraerDatos($plano, $tipo) : []];
    }

    public function detectarTipo(string $plano): ?string
    {
        $normalizado = Str::lower(Str::ascii($plano));
        $mejor = null;
        $mejorPuntaje = 0;

        foreach (self::FIRMAS as $tipo => $frases) {
            $puntaje = 0;
            foreach ($frases as $frase => $peso) {
                if (str_contains($normalizado, $frase)) {
                    $puntaje += $peso;
                }
            }

            if ($puntaje > $mejorPuntaje) {
                $mejor = $tipo;
                $mejorPuntaje = $puntaje;
            }
        }

        return $mejorPuntaje >= 3 ? $mejor : null;
    }

    /** @return array<string, string> */
    private function extraerDatos(string $t, string $tipo): array
    {
        $datos = [];
        $poner = function (string $campo, ?string $valor) use (&$datos) {
            $valor = $valor === null ? '' : trim($valor, " \t\n\r\0\x0B.,;:");
            if ($valor !== '') {
                $datos[$campo] = $valor;
            }
        };

        // Encabezado común.
        $poner('num_exp', $this->buscar('/Expte\.?\s*N[°º]?\s*([\w\/\-\.]+)/iu', $t));
        $poner('num_res', $this->buscar('/RESOLUCI[ÓO]N\s*N[°º]?\s*([\w\/\-\.]+)/iu', $t));
        $poner('fecha_res', $this->primeraFecha($t));

        if (in_array($tipo, ['AplicarPagos', 'ReconocimientoCuotaPagadaDosVeces', 'ReconocimientoCuotaPagadaNoCargada'], true)) {
            return $datos + $this->datosDeNota($t, $tipo);
        }

        // Vivienda: manzana, lote/casa, barrio o plan.
        $poner('manzana', $this->buscar('/MANZANA\s+(\d+)/iu', $t));
        $poner('lote', $this->buscar('/(?:LOTE|CASA)\s+(\d+)/iu', $t));
        $poner('nombre_barrio', $this->buscar('/DEL BARRIO\s+(.+?)\s+CONSIDERANDO/iu', $t));
        $poner('nombre_plan', $this->buscar('/DEL (?:PLAN|EMPRENDIMIENTO)\s+"?([^";]+?)"?\s*(?:-|;|CONSIDERANDO)/iu', $t));

        // Fojas y datos que se repiten en varios tipos.
        $poner('num_foja_solicitud', $this->buscar('/fs\.\s*([\w\/]+)\s+obra solicitud/iu', $t));
        $poner('num_foja_informe', $this->buscar('/fs\.\s*([\w\/]+)\s+(?:obra\s+)?informe/iu', $t));
        $poner('num_foja_dictamen', $this->buscar('/fs\.\s*([\w\/]+)\s+glosa Dictamen/iu', $t)
            ?? $this->buscar('/Dictamen Legal N[°º]?\s*[\w\/\-]+,?\s*fs\.\s*([\w\/]+)/iu', $t));
        $poner('num_dictamen', $this->buscar('/Dictamen Legal N[°º]?\s*([\w\/\-]+)/iu', $t));
        $poner('num_res_adjudicacion', $this->buscar('/(?:adjudicad[ao]|Adjudicaci[óo]n)[^.]{0,80}?Resoluci[óo]n\s*(?:de Adjudicaci[óo]n\s*)?N[°º]?\s*(\d[\w\/\-]*)/iu', $t));
        $poner('num_escritura', $this->buscar('/Escritura P[úu]blica N[°º]?\s*(\d+)/iu', $t));
        $poner('nombre_escribano', $this->buscar('/por ante\s+(.+?),\s+Titular/iu', $t));

        // Titulares. Cancelaciones/Rescisiones: titular actual; Transferencias: el anterior y el nuevo.
        $titulares = $this->titulares($t);
        $clave = in_array($tipo, ['Transferencias', 'Transferencia-Cancelacion'], true) ? '_anterior' : '';
        if ($titulares) {
            $poner('nombre_titular'.$clave, $titulares[0]['nombre']);
            $poner('dni_titular'.$clave, $titulares[0]['dni']);
            if (isset($titulares[1])) {
                $poner('nombre_cotitular'.$clave, $titulares[1]['nombre']);
                $poner('dni_cotitular'.$clave, $titulares[1]['dni']);
            }
        }

        if ($clave) {
            $poner('nombre_titular_nuevo', $this->buscar('/a favor de\s+(?:la Se[ñn]ora|el Se[ñn]or|los Se[ñn]ores)\s+(.+?)(?:\s+DNI|\.|,)/iu', $t));
            $poner('dni_titular_nuevo', $this->buscar('/a favor de\s+(?:la Se[ñn]ora|el Se[ñn]or|los Se[ñn]ores)\s+.+?DNI\s*N[°º]?\s*([\d\.]+)/iu', $t));
        }

        return $datos;
    }

    /** @return array<string, string> */
    private function datosDeNota(string $t, string $tipo): array
    {
        $datos = [];
        $poner = function (string $campo, ?string $valor) use (&$datos) {
            $valor = $valor === null ? '' : trim($valor, " \t\n\r\0\x0B.,;:");
            if ($valor !== '') {
                $datos[$campo] = $valor;
            }
        };

        $poner('codigo_pago', $this->buscar('/C[ÓO]DIGO\s*\(?\s*(\d+)/iu', $t));
        $poner('jefe_computos', $this->buscar('/COMPUTOS\s+ING\.\s+(.+?):/iu', $t));

        if ($tipo === 'AplicarPagos') {
            $poner('monto', $this->numero($this->buscar('/pago especial de \$\s*([\d\.,]+)/iu', $t)));
            $poner('cuotas_adeudadas', $this->buscar('/a las\s+(\d+)\s+cuotas adeudadas/iu', $t));
        } else {
            $poner('quien_suscribe', $this->buscar('/que suscribe:\s*(.+?)\s+DNI/iu', $t));
            $poner('dni_suscribe', $this->buscar('/DNI\s*N[°º]?:\s*([\d\.]+)/iu', $t));
        }

        return $datos;
    }

    /** @return list<array{nombre: string, dni: string}> */
    private function titulares(string $t): array
    {
        if (! preg_match('/Se[ñn]ores?\s+(.+?)\s+DNI\s*N[°º]?\s*([\d\.]+)(?:\s*,?\s+y\s+(.+?)\s*,?\s+DNI\s*N[°º]?\s*([\d\.]+))?/iu', $t, $m)) {
            return [];
        }

        $lista = [['nombre' => trim($m[1]), 'dni' => rtrim($m[2], '.')]];
        if (! empty($m[3])) {
            $lista[] = ['nombre' => trim($m[3]), 'dni' => rtrim($m[4], '.')];
        }

        return $lista;
    }

    private function buscar(string $patron, string $texto): ?string
    {
        return preg_match($patron, $texto, $m) ? $m[1] : null;
    }

    /** "12.345,67" -> "12345.67" (los campos numéricos del formulario usan punto decimal). */
    private function numero(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        return str_replace(',', '.', str_replace('.', '', $valor));
    }

    /** Primera fecha escrita como "20 de mayo de 2026" -> "2026-05-20" (formato del input date). */
    private function primeraFecha(string $t): ?string
    {
        if (! preg_match('/(\d{1,2})\s+de\s+([a-záéíóú]+)\s+(?:de|del)?\s*(\d{4})/iu', $t, $m)) {
            return null;
        }

        $mes = self::MESES[Str::lower(Str::ascii($m[2]))] ?? null;
        if (! $mes || ! checkdate($mes, (int) $m[1], (int) $m[3])) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $m[3], $mes, $m[1]);
    }
}
