<?php

use App\Enums\EstadoDia;
use App\Enums\EstadoPedido;
use Illuminate\Support\Facades\Blade;

test('cada estado con chip tiene un ícono y los que no se muestran no lo llevan', function () {
    foreach (EstadoDia::cases() as $estado) {
        $html = Blade::render('<x-rrhh.estado :estado="$estado" />', ['estado' => $estado]);

        if ($estado === EstadoDia::Futuro) {
            expect($html)->not->toContain('<svg');
        } else {
            expect($estado->icono())->not->toBe('')
                ->and($html)->toContain('sirex-chip-icono');
        }
    }

    foreach (EstadoPedido::cases() as $estado) {
        expect(Blade::render('<x-rrhh.estado :estado="$estado" />', ['estado' => $estado]))
            ->toContain('sirex-chip-icono')
            ->toContain($estado->etiqueta());
    }
});

test('los estados de espera laten y los logrados dibujan el tilde', function () {
    expect(Blade::render('<x-rrhh.estado :estado="$e" />', ['e' => EstadoPedido::PendienteJefe]))->toContain('sirex-chip-espera')
        ->and(Blade::render('<x-rrhh.estado :estado="$e" />', ['e' => EstadoPedido::Confirmado]))->toContain('sirex-chip-logrado')
        ->and(Blade::render('<x-rrhh.estado :estado="$e" />', ['e' => EstadoPedido::Cancelado]))->not->toContain('sirex-chip-espera');
});

test('la línea de tiempo del pedido marca los pasos según el estado', function () {
    $pasos = fn (EstadoPedido $e) => preg_match_all('/data-estado="(\w+)"/', Blade::render('<x-rrhh.linea-pedido :estado="$e" />', ['e' => $e]), $m) ? $m[1] : [];

    expect($pasos(EstadoPedido::PendienteJefe))->toBe(['ok', 'ahora', 'falta'])
        ->and($pasos(EstadoPedido::AprobadoJefe))->toBe(['ok', 'ok', 'ahora'])
        ->and($pasos(EstadoPedido::Confirmado))->toBe(['ok', 'ok', 'ok'])
        ->and($pasos(EstadoPedido::RechazadoJefe))->toBe(['ok', 'no', 'falta'])
        ->and($pasos(EstadoPedido::RechazadoRrhh))->toBe(['ok', 'ok', 'no']);
});

test('el botón animado ejecuta la acción al terminar y respeta confirmación y condición', function () {
    $html = Blade::render('<x-boton-animado accion="$wire.borrar()" confirmar="¿Seguro?" si="$wire.x" />');

    expect($html)
        ->toContain('data-fase')
        ->toContain('$wire.borrar()')
        ->toContain('confirm(')
        ->toContain('$wire.x')
        ->toContain('prefers-reduced-motion')
        ->toContain('sirex-anim-ok');

    expect(Blade::render('<x-boton-animado variante="pase" accion="$wire.a()" etiqueta="Confirmar pase" listo="Enviado" />'))
        ->toContain('sirex-vuela')
        ->toContain('Enviado');
});

test('la pantalla vacía muestra el mensaje', function () {
    expect(Blade::render('<x-vacio icono="buscar">Nada por acá</x-vacio>'))
        ->toContain('sirex-vacio')
        ->toContain('Nada por acá');
});

test('las animaciones están definidas y se apagan con "menos movimiento"', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    foreach (['@keyframes sirex-texto-al-tacho', '@keyframes sirex-toast-entra', '@keyframes sirex-fila-entra', '@keyframes sirex-tiembla', '.sirex-pulso', '.sirex-linea', '.sirex-vacio'] as $pieza) {
        expect($css)->toContain($pieza);
    }

    expect($css)->toMatch('/prefers-reduced-motion: reduce\)\s*\{.*animation-duration: 0\.01ms !important/s');
});

test('los toasts propios reemplazan al de SweetAlert', function () {
    $js = file_get_contents(resource_path('js/app.js'));

    expect($js)->toContain('mostrarToast')->toContain("Livewire.on('toast'")->not->toContain('Swal.fire');
});
