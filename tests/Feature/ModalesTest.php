<?php

// Todas las ventanas comparten el mismo diseño (clases .sirex-modal* en resources/css/app.css).
// Este test evita que vuelva a aparecer un modal armado a mano con su propio fondo y botones.

test('todos los modales usan el diseño compartido', function (string $archivo) {
    $contenido = file_get_contents(resource_path("views/{$archivo}.blade.php"));

    expect($contenido)
        ->toContain('sirex-modal-fondo')
        ->toMatch('/class="sirex-modal[ "]/')
        ->not->toContain('bg-[#001428]/50');
})->with([
    'livewire/modal-ConfirmarBorrado',
    'livewire/modal-filtros',
    'livewire/modal-permisos',
    'livewire/Expedientes/modal-NuevoExpediente',
    'livewire/Expedientes/modal-EditarExpediente',
    'livewire/Expedientes/modal-RealizarPase',
    'livewire/lista-usuario',
    'livewire/resoluciones',
    'livewire/dashboard-calendar',
    'livewire/settings/delete-user-form',
    'livewire/crear-resolucion',
]);

test('el diseño compartido de modales está definido en el CSS', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    foreach (['.sirex-modal-fondo', '.sirex-modal-cabeza', '.sirex-modal-cuerpo', '.sirex-modal-pie', '.sirex-campo', '.sirex-boton-primario', '.sirex-boton-peligro'] as $clase) {
        expect($css)->toContain($clase);
    }

    // El fondo no puede llevar transform: rompería el position: fixed de lo que contenga.
    expect($css)->toMatch('/\.sirex-modal-fondo\s*\{[^}]*animation: sirex-fondo-entrada[^}]*backwards/');
});
