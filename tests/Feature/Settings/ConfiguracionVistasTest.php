<?php

use App\Models\User;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

test('el menú de configuración marca la sección actual y muestra la ficha de la cuenta', function () {
    $this->get('/settings/profile')
        ->assertOk()
        ->assertSee('aria-label="Secciones de configuración"', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee('Tus datos y tu foto')
        ->assertSee('Seguridad de la cuenta')
        ->assertSee('Tema claro u oscuro');
});

test('el perfil permite cambiar la foto desde el círculo y tiene la zona de peligro', function () {
    $this->get('/settings/profile')
        ->assertOk()
        ->assertSee('photo-upload', false)
        ->assertSee('JPG, PNG o WebP, hasta 1 MB.')
        ->assertSee('Eliminar cuenta');
});

test('la contraseña muestra el medidor, los requisitos y el botón de ver', function () {
    $this->get('/settings/password')
        ->assertOk()
        ->assertSee('10 caracteres o más')
        ->assertSee('Las contraseñas coinciden')
        ->assertSee('Mostrar contraseña', false)
        ->assertSee('autocomplete="new-password"', false);
});

test('la apariencia ofrece tres temas con vista previa', function () {
    $this->get('/settings/appearance')
        ->assertOk()
        ->assertSee('role="radiogroup"', false)
        ->assertSee('Claro')
        ->assertSee('Oscuro')
        ->assertSee('Sistema');
});
