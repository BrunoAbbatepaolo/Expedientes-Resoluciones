<?php

namespace App\Services\Rrhh;

use App\Enums\NivelDatoRrhh;
use App\Models\Mitiv\Empleado;
use Illuminate\Support\Carbon;

/**
 * Campos del legajo para mostrar, agrupados por nivel. Solo devuelve los grupos que el
 * nivel incluye (las columnas de niveles superiores ni siquiera se leyeron).
 */
final class CamposLegajo
{
    /** @return list<array{titulo: string, sensible: bool, campos: array<string, string|null>}> */
    public static function grupos(Empleado $e, NivelDatoRrhh $nivel): array
    {
        $fecha = fn ($v) => $v ? Carbon::parse($v)->format('d/m/Y') : null;
        $unir = fn (...$partes) => trim(implode(' ', array_filter($partes, fn ($p) => filled($p)))) ?: null;

        $grupos = [[NivelDatoRrhh::Basico, 'Laborales', [
            'Legajo' => $e->legajo,
            'Oficina' => $e->oficinaRrhh?->nombre,
            'Función' => $e->funcion,
            'Situación laboral' => $e->situacionLaboral?->nombre,
            'Categoría titular' => $e->categoriaTitular?->nombre,
            'Categoría interina' => $e->categoriaInterina?->nombre,
            'Ingreso' => $fecha($e->fecha_ingreso),
            'Horario' => $e->horario,
            'Email' => $e->email,
        ]]];

        if ($nivel->incluye(NivelDatoRrhh::Personal)) {
            $grupos[] = [NivelDatoRrhh::Personal, 'Personales', [
                'DNI' => $e->numero_documento,
                'CUIL' => $e->cuil,
                'Nacimiento' => $unir($fecha($e->fecha_nacimiento), $e->lugar_nacimiento),
                'Sexo' => $e->sexo,
                'Nacionalidad' => $e->nacionalidad,
                'Domicilio' => $e->domicilio,
                'Teléfono' => $e->telefono,
                'Celular' => $e->telefono_celular,
                'Estudios' => $e->nivel_estudio,
                'Matrícula' => $e->matricula_profesional,
                'Obra social' => $unir($e->numero_obra_social, $e->otra_obra_social),
                'IPSS' => $e->numero_ipss,
                'Seguro' => $unir($e->aseguradora ?? $e->otra_aseguradora, $e->numero_poliza ? 'póliza '.$e->numero_poliza : null),
                'Emergencia' => $unir($e->emergencia, $e->telefono_emergencia),
                'Resolución' => $unir($e->resolucion, $fecha($e->fecha_resolucion)),
                'Contratado' => $unir($fecha($e->fecha_contratado), $e->res_dec_contrato),
                'Cónyuge' => $unir($e->conyuge_nombre, $e->conyuge_cuil ? 'CUIL '.$e->conyuge_cuil : null),
                'Observación' => $e->observacion,
            ]];
        }

        if ($nivel->incluye(NivelDatoRrhh::Sensible)) {
            $grupos[] = [NivelDatoRrhh::Sensible, 'Sensibles', [
                'Discapacidad' => filter_var($e->es_discapacitado, FILTER_VALIDATE_BOOLEAN) ? $unir('Sí', $e->tipo_discapacidad, $e->grado_discapacidad) : null,
                'Vigencia certificado' => $fecha($e->vigencia_certificado_discapacidad),
                'Enfermedad' => $e->enfermedad,
                'Tratamiento' => $e->tratamiento,
                'Cuenta bancaria' => $e->nro_cuenta_bancaria,
            ]];
        }

        return array_map(fn ($g) => [
            'titulo' => $g[1],
            'sensible' => $g[0] === NivelDatoRrhh::Sensible,
            'campos' => array_map(fn ($v) => filled($v) ? (string) $v : null, $g[2]),
        ], $grupos);
    }
}
