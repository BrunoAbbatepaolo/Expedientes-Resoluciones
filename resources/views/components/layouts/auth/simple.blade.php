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
                    <img src="{{ asset('images/logo-ipv-simbolo.png') }}" alt="" class="sirex-login-sello size-full drop-shadow-[0_6px_14px_rgba(255,184,26,0.45)]" />
                    <span class="sr-only">{{ config('app.name', 'SiRex') }}</span>
                </a>
                <div class="sirex-login-tarjeta sirex-glass-card flex flex-col gap-6 rounded-[16px] p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
