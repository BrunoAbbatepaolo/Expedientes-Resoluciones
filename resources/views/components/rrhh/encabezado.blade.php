@props(['titulo', 'subtitulo' => null])

<div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $titulo }}</h1>
        @if ($subtitulo)
            <p class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $subtitulo }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
