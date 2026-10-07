@props(['estado'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium '.$estado->clases()]) }}>
    {{ $estado->etiqueta() }}
</span>
