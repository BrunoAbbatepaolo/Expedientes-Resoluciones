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

    // Regla de entrada del IPV: hasta la tolerancia es presente (la del horario del empleado o
    // esta si no trae); pasada la tolerancia es llegada tarde; pasado el límite, cuenta como falta.
    'rrhh_tolerancia_minutos' => (int) env('SIREX_RRHH_TOLERANCIA', 10),
    'rrhh_limite_falta_minutos' => (int) env('SIREX_RRHH_LIMITE_FALTA', 30),

    // Minutos sin fichadas (en horario laboral) para avisar que el lector de relojes no carga.
    'rrhh_reloj_alerta_minutos' => (int) env('SIREX_RRHH_RELOJ_ALERTA', 30),

    // Zona horaria de las fechas sin zona del sistema viejo (fichadas, horarios).
    'rrhh_zona_horaria' => env('SIREX_RRHH_ZONA', 'America/Argentina/Tucuman'),

    // Tipos de "ausencia" del sistema viejo que no son licencias sino regímenes que eximen de
    // fichar (planta justificada, jerárquicos, gabinete, retiro voluntario, ley sindical, etc.):
    // se muestran como "Justificado" y no como "Licencia". Ids de tipo_ausencias relevados el
    // 2026-10-07; RRHH puede ajustarlos con SIREX_RRHH_TIPOS_SIN_FICHAR=77,69,...
    'rrhh_tipos_sin_fichar' => array_map('intval', array_filter(explode(',', (string) env(
        'SIREX_RRHH_TIPOS_SIN_FICHAR', '77,69,80,62,76,65,78,130,138,132,148'
    )))),

    // Tipos de "ausencia" que no afectan la asistencia: solo marcan un período (ej. CONT, la
    // vigencia del contrato). Se ignoran y el día se calcula por fichadas: los contratados
    // fichan todos los días, sin marca es falta.
    'rrhh_tipos_ignorados' => array_map('intval', array_filter(explode(',', (string) env(
        'SIREX_RRHH_TIPOS_IGNORADOS', '131'
    )))),

    // Vacaciones (licencia anual ordinaria). El saldo lo calcula SiRex: la vista del sistema
    // viejo dejó de calcular en 2022. Cuentan como tomadas las ausencias de estos tipos (más
    // las de tipos con afecta_vacaciones) imputadas al período (ausencias.anio); los anticipos
    // son la ausencia "ANT DE VACACIONES" y la inasistencia "ANTICIPO DE LICENCIA".
    'rrhh_tipos_vacaciones' => array_map('intval', array_filter(explode(',', (string) env('SIREX_RRHH_TIPOS_VACACIONES', '96')))),
    'rrhh_tipos_anticipo_vacaciones' => array_map('intval', array_filter(explode(',', (string) env('SIREX_RRHH_TIPOS_ANTICIPO', '54')))),
    'rrhh_inasistencia_anticipo_id' => (int) env('SIREX_RRHH_INASISTENCIA_ANTICIPO', 5),

    // Las vacaciones del período X se toman durante el año X + desfase y vencen al terminar ese
    // año (confirmado por el usuario: en 2026 se gozan las del período 2025).
    'rrhh_vacaciones_desfase' => (int) env('SIREX_RRHH_VACACIONES_DESFASE', 1),

    // Pedido de licencias desde Mi RRHH (spec 7 bis). Apagado por defecto. Los tipos que
    // permiten pedir con fecha pasada aceptan hasta estos días hacia atrás.
    'rrhh_pedidos' => (bool) env('SIREX_RRHH_PEDIDOS', false),
    'rrhh_pedido_dias_retro' => (int) env('SIREX_RRHH_PEDIDO_DIAS_RETRO', 30),

    // Tipos de inasistencia del sistema viejo que genera su proceso de reloj.
    'rrhh_tipo_ausente_id' => (int) env('SIREX_RRHH_TIPO_AUSENTE', 82),
    'rrhh_tipo_tarde_id' => (int) env('SIREX_RRHH_TIPO_TARDE', 7),

];
