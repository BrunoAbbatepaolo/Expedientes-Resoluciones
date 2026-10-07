{{-- Select de filtro con opciones id => nombre. --}}
@props(['opciones' => [], 'vacio' => null, 'etiqueta'])

<label class="block">
    <span class="sr-only">{{ $etiqueta }}</span>
    <select {{ $attributes->merge(['class' => 'w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 dark:border-white/15 dark:bg-[#141c26] dark:text-ipv-ink-dark']) }}>
        @if ($vacio !== null)
            <option value="">{{ $vacio }}</option>
        @endif
        @foreach ($opciones as $valor => $texto)
            <option value="{{ $valor }}">{{ $texto }}</option>
        @endforeach
    </select>
</label>
