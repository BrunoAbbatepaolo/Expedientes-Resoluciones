<?php

namespace App\Services\Resoluciones;

/**
 * Qué tiene cada tipo de resolución (los mismos nombres que resources/views/prototipos/*): cómo se llama, qué datos
 * lleva, cuáles son obligatorios y cómo se validan. Sumar un tipo es agregar su prototipo y sus datos acá.
 */
final class Tipos
{
    /** Tipos de resolución con formulario. */
    public const TODOS = [
        'Cancelaciones', 'Resciciones', 'Transferencias', 'Transferencia-Cancelacion',
        'Rectificacion', 'AplicarPagos', 'ReconocimientoCuotaPagadaDosVeces', 'ReconocimientoCuotaPagadaNoCargada',
        'Cancelacion8042', 'CancelacionFondoReserva', 'OficioJudicial', 'Adjudicacion',
    ];

    public static function nombre(string $tipo): string
    {
        return match ($tipo) {
            'Cancelaciones' => 'Cancelaciones',
            'Resciciones' => 'Rescisiones',
            'Transferencias' => 'Transferencias',
            'Transferencia-Cancelacion' => 'Transferencia y Cancelación',
            'Rectificacion' => 'Rectificación',
            'AplicarPagos' => 'Aplicar pagos',
            'ReconocimientoCuotaPagadaDosVeces' => 'Reconocimiento de cuota pagada dos veces',
            'ReconocimientoCuotaPagadaNoCargada' => 'Reconocimiento de cuota pagada y no cargada',
            'Cancelacion8042' => 'Cancelación por Ley 8042',
            'CancelacionFondoReserva' => 'Cancelación por Fondo de Reserva',
            'OficioJudicial' => 'Oficio judicial (toma de razón)',
            'Adjudicacion' => 'Adjudicación',
            default => $tipo,
        };
    }

    /** Datos de una resolución nueva, todos vacíos. */
    public static function datosVacios(): array
    {
        return [
            'fecha_res' => '',
            'num_exp' => '',
            'num_res' => '',
            'manzana' => '',
            'lote' => '',
            'nombre_barrio' => '',
            'num_foja_solicitud' => '',
            'num_foja_informe' => '',
            'fecha_cancelacion' => '',
            'num_foja_darrd' => '',
            'num_foja_dictamen' => '',
            'nombre_titular' => '',
            'dni_titular' => '',
            'nombre_cotitular' => '',
            'dni_cotitular' => '',
            'num_escritura' => '',
            'fecha_escritura' => '',
            'nombre_escribano' => '',
            'num_foja_boleto' => '',
            'num_dictamen' => '',
            'num_res_adjudicacion' => '',
            'nombre_titular_anterior' => '',
            'dni_titular_anterior' => '',
            'nombre_cotitular_anterior' => '',
            'dni_cotitular_anterior' => '',
            'num_res_reglamentacion' => '',
            'num_res_modificatoria' => '',
            'unidad' => '',
            'sector' => '',
            'estado_civil_titular_nuevo' => '',
            'estado_civil_cotitular_nuevo' => '',
            // Campos específicos de AplicarPagos (nombre_barrio ya está arriba)
            'codigo_pago' => '',
            'causante' => 'DEPARTAMENTO RECURSOS FINANCIEROS',
            'jefe_computos' => '',
            'monto' => '',
            'cuotas_adeudadas' => '',
            'firma_1_nombre' => '',
            'firma_1_cargo' => '',
            'firma_2_nombre' => '',
            'firma_2_cargo' => '',
            'firma_1_dependencia' => '',
            'firma_2_dependencia' => '',
            // Campos específicos de Rectificación
            'texto_donde_dice' => '',
            'res_reglamentacion' => '185/2017',
            'res_modificatoria' => '779/2017',
            'fecha_res_adjudicacion' => '',
            'orden' => '',
            'num_foja_dni' => '',
            'num_foja_resolucion' => '',
            'num_foja_darrd_conf' => '',
            'cantidad_viviendas' => '',
            'cantidad_letras' => '',
            // Campos específicos de Resición
            'nombre_emprendimiento' => '',
            'departamento' => '',
            'monto_deuda' => '',
            'fecha_deuda' => '',
            'fecha_dictamen' => '',
            // Campos específicos de Transferencia
            'nombre_plan' => '',
            'nombre_titular_nuevo' => '',
            'dni_titular_nuevo' => '',
            'fecha_nacimiento_titular' => '',
            'nombre_cotitular_nuevo' => '',
            'dni_cotitular_nuevo' => '',
            'fecha_nacimiento_cotitular' => '',
            'fecha_instrumento' => '',
            'num_foja_plan' => '',
            'num_foja_recursos' => '',
            'num_foja_promocion' => '',
            'num_foja_ratificacion' => '',
            'num_foja_regularizacion' => '',
            'num_cuotas' => '',
            'monto_cuota' => '',
            'tasa_interes' => '',
            'fecha_tasa' => '',
            // Campos específicos de ReconocimientoCuotaPagadaDosVeces
            'quien_suscribe' => '',
            'dni_suscribe' => '',
            'cuota_sin_gastos' => '',
            'nro_ultima_cuota' => '',
            'vencimiento_ultima_cuota' => '',
            'plazo_total_plan' => '',
            // Tablas de los Reconocimiento (filas que se agregan en el documento; ver components/resolucion/tabla)
            'tabla_cuotas_pagadas' => [],
            'tabla_cuotas_a_reconocer' => [],
            // Cancelación por Ley 8042, por Fondo de Reserva, Oficio judicial y Adjudicación
            'ley_condonacion' => '',
            'decreto_condonacion' => '',
            'res_fondo_reserva' => '',
            'modif_fondo_reserva' => '',
            'tratamiento_solicitante' => '',
            'nombre_solicitante' => '',
            'vinculo_solicitante' => '',
            'num_foja_defuncion' => '',
            'nombre_fallecido' => '',
            'dni_fallecido' => '',
            'fecha_fallecimiento' => '',
            'oficina_judicial' => '',
            'caratula' => '',
            'expte_judicial' => '',
            'juzgado' => '',
            'fecha_resolucion_judicial' => '',
            'num_foja_adjudicaciones' => '',
            'porcentaje' => '',
            'fecha_presentacion' => '',
            'num_foja_catastro' => '',
            'num_foja_pagos' => '',
            'precio_vivienda' => '',
            'precio_terreno' => '',
            // Rectificación: adjudicatarios "donde dice" / "debe decir" (se pueden quitar enteras)
            'tabla_donde_dice' => [],
            'sin_tabla_donde_dice' => false,
            'tabla_debe_decir' => [],
            'sin_tabla_debe_decir' => false,

        ];
    }

    /**
     * Datos obligatorios de los tipos cuya plantilla no tiene todos los de reglas() (p. ej. las notas de
     * Aplicar pagos y Reconocimiento no llevan número de resolución ni titular): sin esto no se podría guardar nunca,
     * porque el dato exigido no aparece en el documento. null = se usan las reglas de reglas() tal cual.
     *
     * @return list<string>|null
     */
    public static function obligatorios(string $tipo): ?array
    {
        return match ($tipo) {
            'AplicarPagos' => ['fecha_res', 'num_exp', 'codigo_pago', 'monto', 'cuotas_adeudadas'],
            'ReconocimientoCuotaPagadaDosVeces', 'ReconocimientoCuotaPagadaNoCargada' => ['fecha_res', 'num_exp', 'codigo_pago', 'quien_suscribe', 'dni_suscribe'],
            'Cancelacion8042', 'Adjudicacion' => ['fecha_res', 'num_exp', 'nombre_titular', 'dni_titular', 'num_dictamen'],
            'CancelacionFondoReserva' => ['fecha_res', 'num_exp', 'nombre_fallecido', 'dni_fallecido', 'fecha_fallecimiento', 'num_dictamen'],
            'OficioJudicial' => ['fecha_res', 'num_exp', 'nombre_titular', 'dni_titular', 'nombre_titular_nuevo', 'dni_titular_nuevo', 'num_dictamen'],
            default => null,
        };
    }

    /** Las notas (Aplicar pagos y Reconocimientos) no son resoluciones: nunca llevan número de resolución. */
    public static function esNota(?string $tipo): bool
    {
        return in_array($tipo, ['AplicarPagos', 'ReconocimientoCuotaPagadaDosVeces', 'ReconocimientoCuotaPagadaNoCargada'], true);
    }

    /** Dato donde va el titular que se carga a mano, o null si el tipo no lleva (o no es el causante del expediente). */
    public static function campoTitular(string $tipo): ?string
    {
        return match ($tipo) {
            'Cancelaciones', 'Resciciones', 'Cancelacion8042', 'Adjudicacion' => 'nombre_titular',
            'Transferencias', 'Transferencia-Cancelacion' => 'nombre_titular_nuevo',
            default => null,
        };
    }

    public static function conDictamen(string $tipo): bool
    {
        // En los PDF reales de estos tipos el dictamen está siempre (189 de 189). De Rescisiones no hay muestras: opcional.
        return in_array($tipo, ['Cancelaciones', 'Transferencias', 'Transferencia-Cancelacion', 'Rectificacion'], true);
    }

    public static function conTitularNuevo(string $tipo): bool
    {
        return in_array($tipo, ['Transferencias', 'Transferencia-Cancelacion'], true);
    }

    public static function conTitular(string $tipo): bool
    {
        return ! self::conTitularNuevo($tipo) && $tipo !== 'Rectificacion';
    }

    /**
     * Reglas de validación de los datos del tipo, con 'required' solo en los obligatorios de su plantilla.
     *
     * @param  \Closure  $expedienteExiste  regla que comprueba que el expediente exista (necesita el servicio de mitiv)
     * @return array<string, mixed>
     */
    public static function reglas(string $tipo, \Closure $expedienteExiste): array
    {
        $reglas = self::reglasBase($tipo, $expedienteExiste);
        $obligatorios = self::obligatorios($tipo);
        if ($obligatorios === null) {
            return $reglas;
        }

        foreach ($reglas as $clave => $regla) {
            $lista = array_values(array_filter(
                is_array($regla) ? $regla : explode('|', $regla),
                fn ($r) => ! in_array($r, ['required', 'nullable'], true),
            ));
            $campo = substr($clave, strlen('datos.'));
            $reglas[$clave] = [in_array($campo, $obligatorios, true) ? 'required' : 'nullable', ...$lista];
        }

        return $reglas;
    }

    /** @return array<string, mixed> */
    private static function reglasBase(string $tipo, \Closure $expedienteExiste): array
    {
        return [
            'datos.fecha_res' => 'required|date',
            'datos.num_exp' => ['required', 'string', 'max:50', $expedienteExiste],
            // Opcional: el borrador sale sin número y se asigna al firmar (desde la lista).
            'datos.num_res' => 'nullable|string|max:50',
            // Se guardan en cod_barrio/cod_casa, columnas integer.
            // Hay manzanas y lotes con letra ("B", "6A"): solo si son números se guardan para buscar por barrio/casa.
            'datos.manzana' => 'nullable|string|max:20',
            'datos.lote' => 'nullable|string|max:20',
            'datos.nombre_barrio' => 'nullable|string|max:255',
            'datos.num_foja_solicitud' => 'nullable|string|max:50',
            'datos.num_foja_informe' => 'nullable|string|max:50',
            'datos.fecha_cancelacion' => 'nullable|date',
            'datos.num_foja_dictamen' => 'nullable|string|max:50',
            // El titular que se exige depende del formulario: Transferencias usa el titular
            // nuevo y Rectificación no tiene titular (antes no se podían guardar nunca).
            'datos.nombre_titular' => [self::conTitular($tipo) ? 'required' : 'nullable', 'string', 'max:255'],
            'datos.dni_titular' => [self::conTitular($tipo) ? 'required' : 'nullable', 'string', 'max:20'],
            'datos.nombre_cotitular' => 'nullable|string|max:255',
            'datos.dni_cotitular' => 'nullable|string|max:20',
            'datos.num_escritura' => 'nullable|string|max:50',
            'datos.fecha_escritura' => 'nullable|date',
            'datos.nombre_escribano' => 'nullable|string|max:255',
            // Campos específicos de Resciciones
            'datos.nombre_emprendimiento' => 'nullable|string|max:255',
            'datos.departamento' => 'nullable|string|max:100',
            'datos.monto_deuda' => 'nullable|numeric|min:0',
            'datos.fecha_deuda' => 'nullable|date',
            'datos.fecha_dictamen' => 'nullable|date',
            'datos.num_res_adjudicacion' => 'nullable|string|max:50',
            // Campos específicos de Transferencia
            'datos.nombre_plan' => 'nullable|string|max:255',
            'datos.nombre_titular_nuevo' => [self::conTitularNuevo($tipo) ? 'required' : 'nullable', 'string', 'max:255'],
            'datos.dni_titular_nuevo' => [self::conTitularNuevo($tipo) ? 'required' : 'nullable', 'string', 'max:20'],
            'datos.fecha_nacimiento_titular' => 'nullable|date',
            'datos.nombre_cotitular_nuevo' => 'nullable|string|max:255',
            'datos.dni_cotitular_nuevo' => 'nullable|string|max:20',
            'datos.fecha_nacimiento_cotitular' => 'nullable|date',
            'datos.fecha_instrumento' => 'nullable|date',
            'datos.num_foja_plan' => 'nullable|string|max:50',
            'datos.num_foja_recursos' => 'nullable|string|max:50',
            'datos.num_foja_promocion' => 'nullable|string|max:50',
            'datos.num_foja_ratificacion' => 'nullable|string|max:50',
            'datos.num_foja_regularizacion' => 'nullable|string|max:50',
            'datos.num_cuotas' => 'nullable|integer|min:1',
            'datos.monto_cuota' => 'nullable|numeric|min:0',
            'datos.tasa_interes' => 'nullable|numeric|min:0',
            'datos.fecha_tasa' => 'nullable|date',
            // Campos específicos de Transferencia-Cancelacion
            'datos.num_res_reglamentacion' => 'nullable|string|max:50',
            'datos.num_res_modificatoria' => 'nullable|string|max:50',
            'datos.unidad' => 'nullable|string|max:20',
            'datos.sector' => 'nullable|string|max:20',
            'datos.estado_civil_titular_nuevo' => 'nullable|string|max:30',
            'datos.estado_civil_cotitular_nuevo' => 'nullable|string|max:30',
            // Campos específicos de Rectificación
            'datos.texto_donde_dice' => 'nullable|string',
            'datos.res_reglamentacion' => 'nullable|string|max:20',
            'datos.res_modificatoria' => 'nullable|string|max:20',
            'datos.fecha_res_adjudicacion' => 'nullable|date',
            'datos.orden' => 'nullable|string|max:10',
            'datos.num_foja_darrd' => 'nullable|string|max:20',
            'datos.num_foja_dni' => 'nullable|string|max:20',
            'datos.num_foja_resolucion' => 'nullable|string|max:20',
            'datos.num_foja_darrd_conf' => 'nullable|string|max:20',
            'datos.cantidad_viviendas' => 'nullable|string|max:20',
            'datos.cantidad_letras' => 'nullable|string|max:100',
            // Campos específicos de AplicarPagos
            'datos.codigo_pago' => 'nullable|string|max:20',
            'datos.monto' => 'nullable|string|max:30',
            'datos.cuotas_adeudadas' => 'nullable|string|max:10',
            'datos.causante' => 'nullable|string|max:255',
            'datos.jefe_computos' => 'nullable|string|max:255',
            'datos.firma_1_nombre' => 'nullable|string|max:255',
            'datos.firma_1_cargo' => 'nullable|string|max:255',
            'datos.firma_2_nombre' => 'nullable|string|max:255',
            'datos.firma_2_cargo' => 'nullable|string|max:255',
            'datos.firma_1_dependencia' => 'nullable|string|max:255',
            'datos.firma_2_dependencia' => 'nullable|string|max:255',
            // Cada resolución cita su propio dictamen (en los PDF reales nunca se repite entre expedientes).
            'datos.num_dictamen' => [self::conDictamen($tipo) ? 'required' : 'nullable', 'string', 'max:50'],
            // Campos específicos de los Reconocimiento
            'datos.quien_suscribe' => 'nullable|string|max:255',
            'datos.dni_suscribe' => 'nullable|string|max:20',
            'datos.cuota_sin_gastos' => 'nullable|string|max:30',
            'datos.nro_ultima_cuota' => 'nullable|string|max:10',
            'datos.vencimiento_ultima_cuota' => 'nullable|date',
            'datos.plazo_total_plan' => 'nullable|string|max:30',
            'datos.tabla_cuotas_pagadas' => 'nullable|array|max:50',
            'datos.tabla_cuotas_pagadas.*.*' => 'nullable|string|max:100',
            'datos.tabla_cuotas_a_reconocer' => 'nullable|array|max:50',
            'datos.tabla_cuotas_a_reconocer.*.*' => 'nullable|string|max:100',
            'datos.tabla_donde_dice' => 'nullable|array|max:50',
            'datos.tabla_donde_dice.*.*' => 'nullable|string|max:100',
            'datos.tabla_debe_decir' => 'nullable|array|max:50',
            'datos.tabla_debe_decir.*.*' => 'nullable|string|max:100',
            'datos.sin_tabla_donde_dice' => 'nullable|boolean',
            'datos.sin_tabla_debe_decir' => 'nullable|boolean',
            // Cancelación por Ley 8042, por Fondo de Reserva, Oficio judicial y Adjudicación
            'datos.ley_condonacion' => 'nullable|string|max:30',
            'datos.decreto_condonacion' => 'nullable|string|max:30',
            'datos.res_fondo_reserva' => 'nullable|string|max:30',
            'datos.modif_fondo_reserva' => 'nullable|string|max:60',
            'datos.tratamiento_solicitante' => 'nullable|string|max:30',
            'datos.nombre_solicitante' => 'nullable|string|max:255',
            'datos.vinculo_solicitante' => 'nullable|string|max:60',
            'datos.num_foja_defuncion' => 'nullable|string|max:50',
            'datos.nombre_fallecido' => 'nullable|string|max:255',
            'datos.dni_fallecido' => 'nullable|string|max:20',
            'datos.fecha_fallecimiento' => 'nullable|date',
            'datos.oficina_judicial' => 'nullable|string|max:255',
            'datos.caratula' => 'nullable|string|max:255',
            'datos.expte_judicial' => 'nullable|string|max:50',
            'datos.juzgado' => 'nullable|string|max:255',
            'datos.fecha_resolucion_judicial' => 'nullable|date',
            'datos.num_foja_adjudicaciones' => 'nullable|string|max:50',
            'datos.porcentaje' => 'nullable|numeric|min:0|max:100',
            'datos.fecha_presentacion' => 'nullable|date',
            'datos.num_foja_catastro' => 'nullable|string|max:50',
            'datos.num_foja_pagos' => 'nullable|string|max:50',
            'datos.precio_vivienda' => 'nullable|numeric|min:0',
            'datos.precio_terreno' => 'nullable|numeric|min:0',
        ];
    }

    /** @return array<string, string> */
    public static function mensajes(): array
    {
        return [
            'datos.nombre_titular_nuevo.required' => 'El nombre del nuevo titular es obligatorio',
            'datos.dni_titular_nuevo.required' => 'El DNI del nuevo titular es obligatorio',
            'datos.fecha_res.required' => 'La fecha de resolución es obligatoria',
            'datos.num_exp.required' => 'El número de expediente es obligatorio',
            'datos.nombre_titular.required' => 'El nombre del titular es obligatorio',
            'datos.dni_titular.required' => 'El DNI del titular es obligatorio',
            'datos.num_dictamen.required' => 'El número de dictamen es obligatorio',
            'datos.codigo_pago.required' => 'El código de pago es obligatorio',
            'datos.monto.required' => 'El monto es obligatorio',
            'datos.cuotas_adeudadas.required' => 'La cantidad de cuotas adeudadas es obligatoria',
            'datos.quien_suscribe.required' => 'El nombre de quien suscribe es obligatorio',
            'datos.dni_suscribe.required' => 'El DNI de quien suscribe es obligatorio',
            'datos.*.max' => 'El campo excede la longitud máxima permitida',
            'datos.*.date' => 'El campo debe ser una fecha válida',
        ];
    }
}
