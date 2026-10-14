<?php

namespace App\Services\Resoluciones;

/**
 * Artículos de la parte RESUELVE de una resolución armada desde un prototipo (resources/views/prototipos).
 *
 * Cada artículo es un `<div class="articulo">` con un `<p>` que arranca con "Art. Nº.-". Acá se pueden quitar
 * artículos (por su posición original, empezando en 1) y los que quedan se renumeran de corrido: si se quita
 * el 3, el 4 pasa a ser el 3. Los bloques marcados distinto (p. ej. los artículos citados en una
 * rectificación, con `data-art-fijo`) no cuentan ni se tocan.
 */
final class Articulos
{
    private const PATRON = '#<div class="articulo">(.*?)</div>#s';

    /** Cantidad de artículos que se pueden quitar/renumerar. */
    public static function contar(string $html): int
    {
        return (int) preg_match_all(self::PATRON, $html);
    }

    /**
     * @param  list<int>  $quitados  posiciones originales (1..n) a sacar
     * @param  bool  $controles  true en la vista previa: agrega la "×" de cada artículo y el aviso para restaurar
     */
    public static function procesar(string $html, array $quitados, bool $controles): string
    {
        $posicion = 0;
        $numero = 0;

        $salida = preg_replace_callback(self::PATRON, function (array $m) use (&$posicion, &$numero, $quitados, $controles) {
            $posicion++;

            if (in_array($posicion, $quitados, true)) {
                return '';
            }

            $numero++;
            $interior = preg_replace('/(Art\.\s*)\d+(º\.-)/u', '${1}'.$numero.'${2}', $m[1], 1) ?? $m[1];

            if (! $controles) {
                return '<div class="articulo">'.$interior.'</div>';
            }

            $quitar = '<button type="button" wire:click="quitarArticulo('.$posicion.')" contenteditable="false" '
                .'class="sirex-art-quitar" title="Quitar este artículo" aria-label="Quitar el artículo '.$numero.'">×</button>';

            return '<div class="articulo sirex-art-con-x">'.$quitar.$interior.'</div>';
        }, $html) ?? $html;

        if ($controles && $quitados !== []) {
            $n = count($quitados);
            $aviso = '<div class="sirex-art-aviso" contenteditable="false"><span>'
                .($n === 1 ? 'Quitaste 1 artículo.' : "Quitaste {$n} artículos.")
                .'</span><button type="button" wire:click="restaurarArticulos">Restaurar</button></div>';

            return $aviso.$salida;
        }

        return $salida;
    }
}
