<?php

namespace App\Support;

/** Utilidades para planillas que se abren en Excel. */
final class Csv
{
    /**
     * Excel ejecuta como fórmula lo que empieza con = + - @ (o tab / retorno de carro): se antepone un apóstrofo
     * a todo texto que escribe una persona.
     */
    public static function celda(mixed $valor): mixed
    {
        return is_string($valor) && $valor !== '' && str_contains("=+-@\t\r", $valor[0]) ? "'".$valor : $valor;
    }

    /**
     * @param  array<int, mixed>  $fila
     * @return array<int, mixed>
     */
    public static function fila(array $fila): array
    {
        return array_map([self::class, 'celda'], $fila);
    }
}
