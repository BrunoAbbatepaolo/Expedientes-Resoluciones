<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </flux:main>
</x-layouts.app.sidebar>
