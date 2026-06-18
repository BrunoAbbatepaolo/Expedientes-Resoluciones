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
    if ($number < 20) {
        $units = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];

        return $units[$number];
    }
    if ($number < 30) {
        return 'VEINTI'.($number == 21 ? 'UNO' : convertNumberToLetters($number - 20));
    }
    if ($number < 40) {
        return 'TREINTA Y '.convertNumberToLetters($number - 30);
    }
    if ($number < 50) {
        return 'CUARENTA Y '.convertNumberToLetters($number - 40);
    }
    if ($number < 60) {
        return 'CINCUENTA Y '.convertNumberToLetters($number - 50);
    }
    if ($number < 70) {
        return 'SESENTA Y '.convertNumberToLetters($number - 60);
    }
    if ($number < 80) {
        return 'SETENTA Y '.convertNumberToLetters($number - 70);
    }
    if ($number < 90) {
        return 'OCHENTA Y '.convertNumberToLetters($number - 80);
    }
    if ($number < 100) {
        return 'NOVENTA Y '.convertNumberToLetters($number - 90);
    }
    if ($number < 200) {
        return 'CIENTO '.convertNumberToLetters($number - 100);
    }
    if ($number < 300) {
        return 'DOSCIENTOS '.convertNumberToLetters($number - 200);
    }
    if ($number < 400) {
        return 'TRESCIENTOS '.convertNumberToLetters($number - 300);
    }
    if ($number < 500) {
        return 'CUATROCIENTOS '.convertNumberToLetters($number - 400);
    }
    if ($number < 600) {
        return 'QUINIENTOS '.convertNumberToLetters($number - 500);
    }
    if ($number < 700) {
        return 'SEISCIENTOS '.convertNumberToLetters($number - 600);
    }
    if ($number < 800) {
        return 'SETECIENTOS '.convertNumberToLetters($number - 700);
    }
    if ($number < 900) {
        return 'OCHOCIENTOS '.convertNumberToLetters($number - 800);
    }
    if ($number < 1000) {
        return 'NOVECIENTOS '.convertNumberToLetters($number - 900);
    }
    if ($number < 2000) {
        return 'MIL '.convertNumberToLetters($number - 1000);
    }
    if ($number < 1000000) {
        $thousands = floor($number / 1000);
        $remainder = $number % 1000;
        $thousandsWord = $thousands == 1 ? 'MIL' : convertNumberToLetters($thousands);

        return $thousandsWord.($remainder > 0 ? ' '.convertNumberToLetters($remainder) : '');
    }
    if ($number < 2000000) {
        return 'UN MILLÓN '.convertNumberToLetters($number - 1000000);
    }

    $millions = floor($number / 1000000);
    $remainder = $number % 1000000;

    return convertNumberToLetters($millions).' MILLONES'.($remainder > 0 ? ' '.convertNumberToLetters($remainder) : '');
}
