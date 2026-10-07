<?php

namespace App\Enums;

/**
 * Niveles de datos del legajo (ver docs/spec-modulo-rrhh.md, sección 5).
 *
 * Cada nivel incluye las columnas de los anteriores. password, clave_fiscal,
 * clave_seguridad_social y roles no están en ninguna lista: nunca se leen.
 */
enum NivelDatoRrhh: int
{
    case Basico = 1;
    case Personal = 2;
    case Sensible = 3;

    private const BASICAS = [
        'id', 'legajo', 'apellido', 'nombre', 'email', 'oficina_id', 'situacion_laboral_id',
        'habilitado', 'espasivo', 'funcion', 'categoria_titular_id', 'categoria_interina_id',
        'fecha_ingreso', 'horario', 'card_id',
    ];

    private const PERSONALES = [
        'numero_documento', 'cuil', 'domicilio', 'telefono', 'telefono_celular', 'fecha_nacimiento',
        'lugar_nacimiento', 'sexo', 'nacionalidad', 'nivel_estudio', 'matricula_profesional',
        'numero_obra_social', 'otra_obra_social', 'numero_ipss', 'aseguradora', 'otra_aseguradora',
        'numero_poliza', 'fecha_inicio_poliza', 'fecha_fin_poliza', 'emergencia', 'telefono_emergencia',
        'resolucion', 'fecha_resolucion', 'fecha_contratado', 'res_dec_contrato', 'observacion',
        'conyuge_nombre', 'conyuge_cuil', 'conyuge_fecha_nacimiento',
    ];

    private const SENSIBLES = [
        'es_discapacitado', 'tipo_discapacidad', 'grado_discapacidad', 'vigencia_certificado_discapacidad',
        'enfermedad', 'tratamiento', 'nro_cuenta_bancaria',
    ];

    private const FAMILIAR_PERSONALES = [
        'id', 'empleado_id', 'apellido_nombre', 'fecha_nacimiento', 'sexo', 'numero_documento',
        'parentesco_id', 'establecimiento', 'grado_anio', 'habilitado',
    ];

    private const FAMILIAR_SENSIBLES = [
        'discapacitado', 'posee_certificado_discapacidad', 'vigencia_certificado_discapacidad',
        'percibe_perpension_alimenticia', 'cantidad_pension_alimenticia', 'posee_orden_judicial',
        'posee_guarda_legal', 'posee_curatela', 'posee_sumario_judicial',
    ];

    /** @return list<string> */
    public function columnas(): array
    {
        return match ($this) {
            self::Basico => self::BASICAS,
            self::Personal => [...self::BASICAS, ...self::PERSONALES],
            self::Sensible => [...self::BASICAS, ...self::PERSONALES, ...self::SENSIBLES],
        };
    }

    /** Columnas del grupo familiar (solo desde Personal). @return list<string> */
    public function columnasFamiliar(): array
    {
        return match ($this) {
            self::Basico => [],
            self::Personal => self::FAMILIAR_PERSONALES,
            self::Sensible => [...self::FAMILIAR_PERSONALES, ...self::FAMILIAR_SENSIBLES],
        };
    }

    public function incluye(self $otro): bool
    {
        return $this->value >= $otro->value;
    }

    public function etiqueta(): string
    {
        return match ($this) {
            self::Basico => 'básico',
            self::Personal => 'personal',
            self::Sensible => 'sensible',
        };
    }
}
