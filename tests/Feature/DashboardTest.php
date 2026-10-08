<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('la ventana de la tarea se puede mover y no se cierra al soltar el arrastre fuera de ella', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('dashboard-calendar')
        ->call('openModal', '2026-10-15')
        ->assertSee('Nueva Tarea')
        ->assertSeeHtml('x-ref="ventana"')
        ->assertSeeHtml('x-on:pointerdown="empezar($event)"')
        ->assertSeeHtml('x-on:dblclick="x = 0; y = 0"')
        // El fondo solo cierra si el clic empezó en el fondo (si no, arrastrar y soltar afuera la cerraría).
        ->assertSeeHtml('x-on:click.self="if (fuera) $wire.closeModal(); fuera = false"')
        ->assertDontSeeHtml('wire:click.self="closeModal"');
});

test('la animación de entrada de la página no deja un transform que descentra las ventanas emergentes', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    // Con fill-mode "both"/"forwards" el <main> conserva transform y los position:fixed se miden contra él.
    expect($css)->toMatch('/\.sirex-pagina\s*\{\s*animation:[^;]*\bbackwards;/')
        ->and($css)->not->toMatch('/\.sirex-pagina\s*\{\s*animation:[^;]*\b(both|forwards)\b/');
});

test('los controles nativos siguen el modo oscuro (lista de los selectores, calendario de fechas)', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    // Sin color-scheme los selectores con fondo translúcido mostraban texto casi blanco sobre blanco.
    expect($css)->toMatch('/\.dark\s*\{\s*color-scheme:\s*dark;/')
        ->and($css)->toMatch('/\.dark select option[^{]*\{[^}]*background-color:[^;]+;[^}]*color:/');
});
