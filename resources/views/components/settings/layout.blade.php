@props(['heading' => '', 'subheading' => ''])

{{-- Configuración: menú propio (pestañas con scroll en celular, columna con ficha de la cuenta desde 1024px)
     y el contenido en una tarjeta con encabezado. --}}
@php
    $usuario = auth()->user();
    $secciones = [
        'settings.profile' => ['etiqueta' => __('Perfil'), 'detalle' => 'Tus datos y tu foto', 'icono' => '<circle cx="12" cy="8" r="3.5" /><path d="M5 20a7 7 0 0 1 14 0" />'],
        'settings.password' => ['etiqueta' => __('Contraseña'), 'detalle' => 'Seguridad de la cuenta', 'icono' => '<rect x="5" y="11" width="14" height="9" rx="2" /><path d="M8 11V8a4 4 0 0 1 8 0v3" />'],
        'settings.appearance' => ['etiqueta' => __('Apariencia'), 'detalle' => 'Tema claro u oscuro', 'icono' => '<circle cx="12" cy="12" r="4" /><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />'],
    ];
    $iconoActual = collect($secciones)->first(fn ($s, $ruta) => request()->routeIs($ruta))['icono'] ?? $secciones['settings.profile']['icono'];
@endphp

<div class="grid items-start gap-5 lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-7">
    <aside class="min-w-0 space-y-4 lg:sticky lg:top-6">
        {{-- Ficha de la cuenta --}}
        <div class="sirex-glass-card hidden items-center gap-3 rounded-[16px] p-3.5 lg:flex">
            <span class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-[14px] bg-ipv-gold text-[15px] font-bold text-ipv-gold-ink dark:text-ipv-gold-ink-dark">
                @if ($usuario->profile_photo_path && \Illuminate\Support\Facades\Storage::exists($usuario->profile_photo_path))
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($usuario->profile_photo_path) }}" alt="" class="h-full w-full object-cover">
                @else
                    {{ $usuario->initials() }}
                @endif
            </span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $usuario->nombre }} {{ $usuario->apellido }}</span>
                <span class="block truncate text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $usuario->email }}</span>
            </span>
        </div>

        <nav aria-label="Secciones de configuración"
            class="-mx-4 flex gap-1.5 overflow-x-auto px-4 pb-1 [scrollbar-width:none] sm:-mx-6 sm:px-6 lg:mx-0 lg:flex-col lg:gap-0.5 lg:overflow-visible lg:px-0 lg:pb-0">
            @foreach ($secciones as $ruta => $s)
                @php($activa = request()->routeIs($ruta))
                <a href="{{ route($ruta) }}" wire:navigate
                    @if ($activa) aria-current="page" @endif
                    class="sirex-nav-item shrink-0 !w-auto max-lg:before:hidden lg:!w-full {{ $activa ? 'is-active' : '' }}">
                    <span class="sirex-nav-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $s['icono'] !!}</svg></span>
                    <span class="min-w-0">
                        <span class="block whitespace-nowrap">{{ $s['etiqueta'] }}</span>
                        <span class="hidden truncate text-[11px] font-normal opacity-60 lg:block">{{ $s['detalle'] }}</span>
                    </span>
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="sirex-glass-card min-w-0 rounded-[18px] p-5 sm:p-7">
        <div class="flex items-start gap-3.5 border-b border-ipv-blue/10 pb-5 dark:border-white/10">
            <span class="sirex-modal-icono sirex-modal-icono-oro">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $iconoActual !!}</svg>
            </span>
            <div class="min-w-0">
                <h2 class="text-lg font-bold tracking-tight text-ipv-ink dark:text-ipv-ink-dark">{{ $heading }}</h2>
                <p class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $subheading }}</p>
            </div>
        </div>

        <div class="mt-6 w-full max-w-xl">
            {{ $slot }}
        </div>
    </div>
</div>
