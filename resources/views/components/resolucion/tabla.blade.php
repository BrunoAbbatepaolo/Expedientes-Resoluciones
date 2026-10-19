{{--
    Tabla con filas que se agregan y quitan, guardada en $datos[campo] como lista de filas.
    'columnas': ['clave' => 'Título'] o ['clave' => ['Título', '$']] (el segundo valor va delante del dato).
--}}
@aware(['editable' => false, 'datos' => [], 'marcadores' => false])
@props(['campo', 'columnas'])
@php
    $columnas = array_map(fn ($c) => is_array($c) ? ['titulo' => $c[0], 'prefijo' => $c[1] ?? ''] : ['titulo' => $c, 'prefijo' => ''], $columnas);
    $vacia = array_fill_keys(array_keys($columnas), '');
    $filas = array_values(array_filter(
        is_array($datos[$campo] ?? null) ? $datos[$campo] : [],
        fn ($fila) => is_array($fila) && implode('', array_map(fn ($v) => trim((string) $v), $fila)) !== '',
    ));
    $lista = "\$wire.datos['{$campo}']";
    // Personalizado: filas vacías para escribir encima.
    if ($marcadores && $filas === []) {
        $filas = [$vacia, $vacia, $vacia];
    }
@endphp
<div @if ($editable) x-data x-init="if (! Array.isArray({{ $lista }}) || ! {{ $lista }}.length) {{ $lista }} = [@js($vacia)]" @endif>
    <table class="tabla-resolucion" style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:13px;">
        <thead>
            <tr style="background:#1e3a5f; color:white;">
                @foreach ($columnas as $columna)
                    <th style="border:1px solid #ccc; padding:6px; text-align:center;">{{ $columna['titulo'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if ($editable)
                <template x-for="(fila, i) in {{ $lista }}" :key="i">
                    <tr>
                        @foreach ($columnas as $clave => $columna)
                            <td style="border:1px solid #ccc; padding:4px; text-align:center;">{{ $columna['prefijo'] }}<input type="text" x-model="fila['{{ $clave }}']" aria-label="{{ $columna['titulo'] }}" class="campo-plantilla" style="width:{{ $columna['prefijo'] ? '85%' : '100%' }}; text-align:center;" /></td>
                        @endforeach
                    </tr>
                </template>
            @else
                @foreach ($filas as $fila)
                    <tr>
                        @foreach ($columnas as $clave => $columna)
                            <td style="border:1px solid #ccc; padding:4px; text-align:center;">{{ ($fila[$clave] ?? '') !== '' ? $columna['prefijo'].$fila[$clave] : '' }}@if ($marcadores && ($fila[$clave] ?? '') === '')&nbsp;@endif</td>
                        @endforeach
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
    @if ($editable)
        <div class="flex justify-center gap-2 mt-2 mb-4">
            <button type="button" title="Agregar fila" aria-label="Agregar fila"
                x-on:click.stop="{{ $lista }}.push(@js($vacia))"
                class="w-9 h-9 rounded-full bg-emerald-500 text-white text-xl font-bold leading-none shadow-sm hover:bg-emerald-600 flex items-center justify-center">+</button>
            <button type="button" title="Quitar la última fila" aria-label="Quitar la última fila"
                x-on:click.stop="
                    const filas = {{ $lista }};
                    if (filas.length <= 1) return;
                    const ultima = filas[filas.length - 1];
                    if (Object.values(ultima).some((v) => String(v ?? '').trim() !== '') && ! confirm('¿Eliminar esta fila?')) return;
                    filas.pop();
                "
                class="w-9 h-9 rounded-full bg-rose-500 text-white text-xl font-bold leading-none shadow-sm hover:bg-rose-600 flex items-center justify-center">−</button>
        </div>
    @endif
</div>
