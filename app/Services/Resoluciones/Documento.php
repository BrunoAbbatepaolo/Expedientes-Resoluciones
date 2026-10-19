<?php

namespace App\Services\Resoluciones;

use Illuminate\Support\Facades\View;

/**
 * Arma el HTML de una resolución desde su prototipo (resources/views/prototipos/{tipo}).
 *
 * Con $editable cada dato del documento es un input ligado a $wire.datos (ver components/resolucion/campo):
 * los que muestran el mismo dato se actualizan juntos. Sin $editable sale el texto final, que es lo que se
 * guarda y se imprime. Se arma siempre en el servidor desde los datos, nunca desde el DOM del cliente.
 */
final class Documento
{
    public static function existe(string $tipo): bool
    {
        return View::exists("prototipos.{$tipo}");
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  list<int>  $articulosQuitados
     */
    public static function html(string $tipo, array $datos, array $articulosQuitados = [], bool $editable = false): string
    {
        $html = View::make("prototipos.{$tipo}", array_merge($datos, [
            'formatearFecha' => 'formatearFecha',
            'formatearFechaLarga' => 'formatearFechaLarga',
            'formatearMoneda' => 'formatearMoneda',
            'num2letras' => 'num2letras',
            'editable' => $editable,
            'datos' => $datos,
        ]))->render();

        return Articulos::procesar($html, $articulosQuitados, $editable);
    }
}
