<div class="flex items-start max-md:flex-col">
    <div class="mr-10 w-full pb-4 md:w-[220px]">
        <nav class="flex flex-col gap-0.5">
            @php
                $settingsLinks = [
                    'settings.profile' => __('Perfil'),
                    'settings.password' => __('Contraseña'),
                    'settings.appearance' => __('Apariencia'),
                ];
            @endphp
            @foreach ($settingsLinks as $route => $label)
                <a href="{{ route($route) }}" wire:navigate
                    class="rounded-lg px-3 py-2 text-sm {{ request()->routeIs($route)
                        ? 'font-semibold bg-ipv-gold/20 text-[#7a5200] dark:bg-ipv-gold/15 dark:text-ipv-gold-light'
                        : 'font-medium text-ipv-ink/75 hover:bg-black/[0.03] dark:text-ipv-ink-dark/82 dark:hover:bg-white/5' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    <div class="border-t border-ipv-blue/10 dark:border-white/10 md:hidden"></div>

    <div class="flex-1 self-stretch max-md:pt-6">
        <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $heading ?? '' }}</h2>
        <p class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $subheading ?? '' }}</p>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
