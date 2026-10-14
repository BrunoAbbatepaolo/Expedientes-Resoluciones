<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased sirex-shell text-ipv-ink dark:text-ipv-ink-dark">
        {{-- Fondo vivo: manchas de color que derivan y motas que suben (todo transform/opacity) --}}
        <div class="sirex-login-fondo" aria-hidden="true">
            <span class="sirex-login-mancha sirex-login-mancha-1"></span>
            <span class="sirex-login-mancha sirex-login-mancha-2"></span>
            <span class="sirex-login-mancha sirex-login-mancha-3"></span>
            @foreach ([['10%', '6px', '10s', '0s'], ['24%', '4px', '12s', '2s'], ['41%', '7px', '9s', '4s'], ['58%', '5px', '11s', '1s'], ['72%', '4px', '13s', '3s'], ['90%', '6px', '10.5s', '5s']] as [$x, $t, $d, $r])
                <span class="sirex-login-mota" style="--x: {{ $x }}; --t: {{ $t }}; --d: {{ $d }}; --r: {{ $r }};"></span>
            @endforeach
        </div>

        <div class="relative flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-4">
                <a href="{{ route('home') }}" class="sirex-login-logo mx-auto mb-4" wire:navigate>
                    <span class="sirex-login-halo"></span>
                    <span class="sirex-login-halo"></span>
                    <span class="sirex-login-sello flex size-14 items-center justify-center rounded-2xl bg-ipv-gold shadow-lg shadow-ipv-gold/40">
                        <svg class="size-7 text-ipv-blue-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M6.7 13.8a42 42 0 0 1 10.6 0M6.3 18h11.4m-1.1-4.2a48 48 0 0 1 1.9.2M17.7 18l.2 2.5a1.1 1.1 0 0 1-1.1 1.2H7.2a1.1 1.1 0 0 1-1.1-1.2L6.3 18m11.3 0h1.1a2.25 2.25 0 0 0 2.3-2.25V9.5c0-1.1-.8-2-1.8-2.2M6.3 18H5.3a2.25 2.25 0 0 1-2.3-2.25V9.5c0-1.1.8-2 1.8-2.2m10.5 0a48 48 0 0 0-10.5 0M18 3.4v3.7" />
                        </svg>
                    </span>
                    <span class="sr-only">{{ config('app.name', 'SiRex') }}</span>
                </a>
                <div class="sirex-login-tarjeta sirex-glass-card flex flex-col gap-6 rounded-[16px] p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
