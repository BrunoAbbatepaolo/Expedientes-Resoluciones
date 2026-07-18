@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <h1 class="text-xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $title }}</h1>
    <p class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $description }}</p>
</div>
