<?php

return [

    // Pases entre oficinas dentro de SiRex (bandeja "Entrantes", "Realizar pase").
    // Desactivado: los pases oficiales se hacen en el sistema viejo y SiRex los muestra
    // desde mitivdb. El código queda por si en el futuro SiRex reemplaza al viejo.
    'pases_internos' => (bool) env('SIREX_PASES_INTERNOS', false),

    // Días sin movimiento a partir de los cuales un expediente se considera demorado.
    'dias_demora' => (int) env('SIREX_DIAS_DEMORA', 30),

];
