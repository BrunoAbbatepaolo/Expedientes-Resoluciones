<?php

// Solo se sobreescribe lo que difiere de los valores por defecto de Livewire 4
// (el resto se toma del config del paquete).
return [

    // Livewire 4 usa 'layouts::app' por defecto; el layout de esta app vive en
    // resources/views/components/layouts/app.blade.php.
    'component_layout' => 'components.layouts.app',

    // Barra de progreso de wire:navigate con el azul institucional.
    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#00519e',
    ],

];
