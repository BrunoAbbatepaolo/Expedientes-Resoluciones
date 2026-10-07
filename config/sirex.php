<?php

return [

    // Pases entre oficinas dentro de SiRex (bandeja "Entrantes", "Realizar pase").
    // Desactivado: los pases oficiales se hacen en el sistema viejo y SiRex los muestra
    // desde mitivdb. El código queda por si en el futuro SiRex reemplaza al viejo.
    'pases_internos' => (bool) env('SIREX_PASES_INTERNOS', false),

    // Días sin movimiento a partir de los cuales un expediente se considera demorado.
    'dias_demora' => (int) env('SIREX_DIAS_DEMORA', 30),

    // Solo cuentan como "activos" (y pueden estar demorados) los expedientes que llegaron
    // a la oficina en este período. Lo anterior se considera archivo histórico.
    'dias_actividad' => (int) env('SIREX_DIAS_ACTIVIDAD', 365),

    // Módulo RRHH (docs/spec-modulo-rrhh.md). Fuente de datos: 'mitiv' = consulta de solo
    // lectura del sistema viejo (fase 1). 'sirex' = tablas propias (fase 2, aún no disponible).
    'rrhh_fuente' => env('SIREX_RRHH_FUENTE', 'mitiv'),

    // Minutos de tolerancia de entrada cuando el horario del empleado no trae la suya.
    'rrhh_tolerancia_minutos' => (int) env('SIREX_RRHH_TOLERANCIA', 30),

    // Minutos sin fichadas (en horario laboral) para avisar que el lector de relojes no carga.
    'rrhh_reloj_alerta_minutos' => (int) env('SIREX_RRHH_RELOJ_ALERTA', 30),

    // Zona horaria de las fechas sin zona del sistema viejo (fichadas, horarios).
    'rrhh_zona_horaria' => env('SIREX_RRHH_ZONA', 'America/Argentina/Tucuman'),

    // Tipos de inasistencia del sistema viejo que genera su proceso de reloj.
    'rrhh_tipo_ausente_id' => (int) env('SIREX_RRHH_TIPO_AUSENTE', 82),
    'rrhh_tipo_tarde_id' => (int) env('SIREX_RRHH_TIPO_TARDE', 7),

];
