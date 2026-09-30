<x-layouts.app :title="__('Dashboard')">
    <div class="w-full">
        <div class="container mx-auto max-w-7xl px-3 sm:px-4 lg:px-6">
            <livewire:dashboard-panel />
            <div class="mt-6">
                <livewire:tablero-ipv />
            </div>
        </div>
    </div>
</x-layouts.app>