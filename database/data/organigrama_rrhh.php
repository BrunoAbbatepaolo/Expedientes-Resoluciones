<?php

/*
 * Dependencia entre oficinas del catálogo RRHH (mitivdb.oficinas), armada desde el
 * Organigrama Funcional del IPV (Res. 0373/2003, versión 06/06/2022).
 *
 * id => [nombre esperado en mitivdb, id de la oficina superior]
 *
 * Lo usa `php artisan rrhh:cargar-organigrama`: solo aplica una fila si el nombre actual
 * coincide, para no colgar mal una oficina si los ids cambiaron. Oficinas sin equivalente
 * en el organigrama (Inspección Médica, PROCREAR, PROMEBA, UEMS, UECELT, Comisión de
 * Servicios, Adscriptos, Retiro Voluntario, Lic. sin goce, Registro General, etc.)
 * no figuran: se cargan a mano en la pantalla Organigrama.
 */

return [
    1 => ['SUB INTERVENCION', 68],

    // Área Social (no tiene oficina propia en el catálogo: sus direcciones cuelgan de la Subintervención)
    79 => ['DIRECCION DE ADJUDICACIONES', 1],
    12 => ['SUB. DIREC. DE ADJUDICACIONES', 79],
    14 => ['DPTO.ADJUDICACIONES', 12],
    15 => ['SECC.INSCRIPCIONES', 14],
    16 => ['DPTO.PROMOCION SOCIAL', 12],
    17 => ['SECC.TRANSFERENCIAS', 16],
    19 => ['DIRECCION PROGRAMAS ESPECIALES', 1],
    20 => ['SUB. DIRECCCION DE PROG. ESPECIALES', 19],
    78 => ['DPTO. PROGRAMAS ESPECIALES', 20],
    74 => ['COORD. DE PROG. SOC.NO TRADICIONALES', 20],
    18 => ['PROMAT', 74],
    21 => ['DPTO.INVESTIG.SOCIAL', 20],

    // Área Económico Financiera
    75 => ['AREA/SUB-DIRECCIÓN ECONÓMICA FINANCIERA', 1],
    27 => ['DPTO.CONTADURIA', 75],
    32 => ['DPTO.PEF', 75],
    73 => ['DTO.PRESUP. Y CONTABILIDAD PATRIMONIAL', 27],
    29 => ['SECC.LIQUIDACIONES', 27],
    30 => ['SECC.TESORERIA', 27],
    31 => ['SECC.COMPRAS', 27],
    33 => ['SECC.INTENDENCIA', 32],
    34 => ['SECC.AUTOMOTORES', 32],

    // Área Legal
    35 => ['AREA LEGAL', 1],
    77 => ['SUBDIREC.AREA LEGAL', 35],
    37 => ['DPTO.ASESORAMIENTO', 77],
    38 => ['DPTO.JUDICIAL', 77],
    39 => ['DPTO. OBRAS', 77],

    // Área Recupero y Regularización Dominial
    40 => ['AREA/SUB.DIR.AREA REC.Y REG.DO', 1],
    41 => ['DPTO.RECURSOS FINANCIEROS', 40],
    42 => ['SECCION INGRESOS', 41],
    43 => ['SECC.CONTROL DE PAGOS', 41],
    44 => ['DPTO.REGULARIZACION DOMINIAL', 40],
    45 => ['SECCION ESCRIBANIA', 44],
    46 => ['DPTO.RECUPERO DE VIVIENDA', 40],

    // Área Planeamiento
    48 => ['AREA-DIRECC.AREA PLANEAMIENTO', 1],
    47 => ['SUB-DIRECC.AREA PLANEAMIENTO', 48],
    49 => ['DPTO.DESARROLLO URB.Y RURAL', 47],
    50 => ['DPTO. CATASTRO', 47],
    51 => ['SECC.BANCO DE TIERRAS', 49],
    52 => ['SECC. ORDENAMIENTO TERRITORIAL', 49],
    54 => ['SECC.AGRIMENSURA', 50],

    // Área Técnica
    55 => ['AREA TECNICA', 1],
    56 => ['SUB-DIRECC.AREA TECNICA', 55],
    57 => ['DPTO.ESTUDIOS Y PROYECTOS', 56],
    62 => ['DPTO.INVEST.TECNOLOGICA', 56],
    61 => ['DPTO.CONSTRUCCIONES', 56],
    63 => ['DPTO.OBRAS POR ADMINISTRACION', 56],
    58 => ['SECCION COSTOS', 57],
    59 => ['SECC.INFRAESTRUCTURA', 57],
    60 => ['SECCION VIVIENDA', 57],
    2 => ['COORD. DE OBRAS', 61],

    // Secretaría General
    8 => ['SECRETARIA GENERAL', 1],
    80 => ['DPTO. RECURSOS HUMANOS', 8],
    9 => ['DPTO. DESPACHO', 8],
    10 => ['SECC.MESA DE ENTRADAS', 9],
    11 => ['SECC. ARCHIVO', 9],

    // Dependen directamente de la Intervención / Subintervención
    5 => ['DPTO. COMPUTOS', 1],
    7 => ['COORD. OPERATIVA ADMINISTRATIVA (COA)', 1],
    3 => ['DPTO. COMUNICACION INSTITUCIONAL', 1],
    4 => ['DPTO. CONTROL DE GESTION', 1],
    84 => ['UGRL - SITRAVI', 1],
];
