{{--
    Firmante N de la resolución: en el editor, lista desplegable con los firmantes activos (Administración → Firmantes);
    al elegir uno se copian nombre, cargo y dependencia a datos.firma_N_* (lo que se guarda e imprime).
    'predeterminado': nombre del firmante que viene elegido si todavía no se eligió ninguno.
--}}
@aware(['editable' => false, 'datos' => []])
@props(['numero', 'predeterminado' => '', 'claseNombre' => '', 'claseCargo' => ''])
@php
    $lista = \App\Models\Firmante::query()->paraElegir()->get()->map->paraResolucion()->values()->all();
    $clave = fn (string $parte) => "firma_{$numero}_{$parte}";
    $elegido = collect($lista)->firstWhere('nombre', $predeterminado) ?? ($lista[0] ?? ['nombre' => $predeterminado, 'cargo' => '', 'dependencia' => '']);
    $texto = fn (string $parte) => trim((string) ($datos[$clave($parte)] ?? '')) !== '' || trim((string) ($datos[$clave('nombre')] ?? '')) !== ''
        ? (string) ($datos[$clave($parte)] ?? '')
        : (string) ($elegido[$parte] ?? '');
    $w = fn (string $parte) => "\$wire.datos['".$clave($parte)."']";
@endphp
@if ($editable)
    <div x-data="{
            lista: @js($lista),
            elegir(i) {
                const f = this.lista[i];
                if (! f) return;
                {{ $w('nombre') }} = f.nombre;
                {{ $w('cargo') }} = f.cargo;
                {{ $w('dependencia') }} = f.dependencia;
            },
        }"
        x-init="if (! {{ $w('nombre') }}) elegir(Math.max(0, lista.findIndex((f) => f.nombre === @js($predeterminado))))">
        <div class="{{ $claseNombre }}">
            @if ($lista === [])
                <span class="campo-derivado" title="Se cargan en Administración → Firmantes">Sin firmantes cargados</span>
            @else
                <select class="campo-plantilla campo-firmante" aria-label="Firmante {{ $numero }}" data-campo="{{ $clave('nombre') }}"
                    x-effect="$el.value = String(lista.findIndex((f) => f.nombre === {{ $w('nombre') }}))"
                    x-on:change="elegir(Number($event.target.value))">
                    <option value="-1" disabled>Elegir firmante…</option>
                    @foreach ($lista as $i => $f)
                        <option value="{{ $i }}">{{ $f['nombre'] }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="{{ $claseCargo }}" x-text="{{ $w('cargo') }}">{{ $texto('cargo') }}</div>
        <div class="{{ $claseCargo }}" x-text="{{ $w('dependencia') }}">{{ $texto('dependencia') }}</div>
    </div>
@else
    <div class="{{ $claseNombre }}">{{ $texto('nombre') }}</div>
    <div class="{{ $claseCargo }}">{{ $texto('cargo') }}</div>
    @if ($texto('dependencia') !== '')
        <div class="{{ $claseCargo }}">{{ $texto('dependencia') }}</div>
    @endif
@endif
