{{-- Invitados (sin sesión) usan el layout de auth: el de la app lee auth()->user(). --}}
<x-dynamic-component :component="auth()->check() ? 'layouts.app' : 'layouts.auth'" title="Acceso denegado">
    <div class="sirex-403 flex min-h-[70vh] flex-col items-center justify-center gap-6 overflow-hidden px-4 py-24 text-center">

        {{-- Motas decorativas que suben por el fondo --}}
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            @foreach ([['8%', '6px', '9s', '0s', '18px'], ['22%', '4px', '11s', '2s', '-14px'], ['38%', '7px', '8s', '4s', '22px'], ['57%', '5px', '10s', '1s', '-20px'], ['73%', '4px', '12s', '3s', '16px'], ['89%', '6px', '9.5s', '5s', '-12px']] as [$x, $t, $d, $r, $v])
                <span class="sirex-403-mota" style="--x: {{ $x }}; --t: {{ $t }}; --d: {{ $d }}; --r: {{ $r }}; --v: {{ $v }};"></span>
            @endforeach
        </div>

        {{-- Candado con ondas de alarma --}}
        <div class="relative flex size-28 items-center justify-center" aria-hidden="true">
            <span class="sirex-403-onda"></span>
            <span class="sirex-403-onda"></span>
            <span class="sirex-403-onda"></span>
            <div class="sirex-403-candado relative flex size-24 items-center justify-center rounded-full bg-ipv-magenta/10 text-ipv-magenta shadow-[0_0_40px_rgba(207,41,114,0.25)] dark:bg-ipv-magenta/20 dark:text-ipv-magenta-light">
                <svg class="size-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path class="sirex-403-arco" d="M8 10.5V7a4 4 0 0 1 8 0v3.5" />
                    <rect x="4.5" y="10.5" width="15" height="9.5" rx="2" />
                    <circle class="sirex-403-led" cx="12" cy="15.2" r="1.2" fill="currentColor" stroke="none" />
                </svg>
            </div>
        </div>


        <div class="space-y-2">
            <h1 class="sirex-403-item text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark" style="--retraso: 350ms">Acceso denegado</h1>
            <p class="sirex-403-item mx-auto max-w-sm text-sm text-ipv-ink/65 dark:text-ipv-ink-dark/65" style="--retraso: 500ms">
                No tenés permiso para entrar a esta sección. Si creés que es un error, pedile acceso a un administrador.
            </p>
        </div>

        <div class="sirex-403-item mt-1 flex flex-wrap items-center justify-center gap-3" style="--retraso: 650ms">
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" wire:navigate
                class="sirex-403-boton rounded-lg bg-ipv-blue px-5 py-2.5 text-sm font-semibold text-white hover:bg-ipv-blue-dark">
                {{ auth()->check() ? 'Volver al panel' : 'Ir a iniciar sesión' }}
            </a>
            <button type="button" onclick="history.length > 1 ? history.back() : (location.href = '{{ auth()->check() ? route('dashboard') : route('login') }}')"
                class="rounded-lg border border-ipv-blue/30 px-5 py-2.5 text-sm font-semibold text-ipv-blue transition hover:-translate-y-0.5 hover:bg-ipv-blue/10 dark:border-ipv-blue-light/30 dark:text-ipv-blue-light">
                Volver atrás
            </button>
        </div>
    </div>
</x-dynamic-component>
