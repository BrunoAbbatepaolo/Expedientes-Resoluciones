<?php

use Carbon\Carbon;

if (! function_exists('formatearFecha')) {
    function formatearFecha($fecha): string
    {
        if (empty($fecha)) {
            return '';
        }
        try {
            return Carbon::parse($fecha)->format('d-m-Y');
        } catch (\Exception $e) {
            return '';
        }
    }
}

if (! function_exists('formatearFechaLarga')) {
    function formatearFechaLarga($fecha): string
    {
        if (empty($fecha)) {
            return '';
        }
        try {
            return Carbon::parse($fecha)->locale('es_AR')->translatedFormat('j \d\e F \d\e\l Y');
        } catch (\Exception $e) {
            return '';
        }
    }
}

if (! function_exists('formatearMoneda')) {
    function formatearMoneda($monto): string
    {
        if (empty($monto)) {
            return '';
        }
        try {
            return number_format((float) str_replace(['$', ','], '', $monto), 2, ',', '.');
        } catch (\Exception $e) {
            return $monto;
        }
    }
}

if (! function_exists('num2letras')) {
    function num2letras($numero): string
    {
        if (empty($numero)) {
            return '';
        }
        $numero = str_replace(['$', ',', ' '], '', $numero);
        $numero = (float) $numero;
        if ($numero == 0) {
            return 'CERO';
        }

        $integerPart = floor($numero);
        $decimalPart = round(($numero - $integerPart) * 100);

        $letters = convertNumberToLetters($integerPart);
        $result = $letters.' con '.str_pad($decimalPart, 2, '0', STR_PAD_LEFT).'/100';

        return $result;
    }
}

function convertNumberToLetters(int $number): string
{
    if ($number < 0) {
        return 'MENOS '.convertNumberToLetters(-$number);
    }
    if ($number == 0) {
        return '';
    }

    if ($number < 1000) {
        return convertirCentenas($number);
    }

    if ($number < 1000000) {
        $miles = intdiv($number, 1000);
        $resto = $number % 1000;
        // 1000 = "MIL" (no "UN MIL"); 21000 = "VEINTIÚN MIL".
        $palabra = $miles == 1 ? 'MIL' : apocoparUno(convertirCentenas($miles)).' MIL';

        return trim($palabra.' '.convertirCentenas($resto));
    }

    $millones = intdiv($number, 1000000);
    $resto = $number % 1000000;
    $palabra = $millones == 1 ? 'UN MILLÓN' : apocoparUno(convertNumberToLetters($millones)).' MILLONES';

    return trim($palabra.' '.convertNumberToLetters($resto));
}

/**
 * 0-999 en letras. 100 = "CIEN", 30 = "TREINTA" (sin "Y" colgando).
 */
function convertirCentenas(int $number): string
{
    $hasta29 = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
        'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE',
        'VEINTE', 'VEINTIUNO', 'VEINTIDÓS', 'VEINTITRÉS', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISÉIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];
    $decenas = [3 => 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $centenas = [1 => 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

    if ($number == 100) {
        return 'CIEN';
    }

    $partes = [];
    if ($number >= 100) {
        $partes[] = $centenas[intdiv($number, 100)];
        $number %= 100;
    }

    if ($number >= 30) {
        $unidad = $number % 10;
        $partes[] = $decenas[intdiv($number, 10)].($unidad ? ' Y '.$hasta29[$unidad] : '');
    } elseif ($number > 0) {
        $partes[] = $hasta29[$number];
    }

    return implode(' ', $partes);
}

/**
 * "UNO" delante de MIL/MILLONES se apocopa: "VEINTIÚN MIL", "TREINTA Y UN MIL".
 */
function apocoparUno(string $letras): string
{
    return preg_replace(['/VEINTIUNO$/u', '/\bUNO$/u'], ['VEINTIÚN', 'UN'], $letras);
}
