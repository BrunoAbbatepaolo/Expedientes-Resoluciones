<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased sirex-shell text-ipv-ink dark:text-ipv-ink-dark">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="mb-1 flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex size-11 items-center justify-center rounded-[10px] bg-ipv-gold">
                        <svg class="size-5 text-ipv-blue-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M6.7 13.8a42 42 0 0 1 10.6 0M6.3 18h11.4m-1.1-4.2a48 48 0 0 1 1.9.2M17.7 18l.2 2.5a1.1 1.1 0 0 1-1.1 1.2H7.2a1.1 1.1 0 0 1-1.1-1.2L6.3 18m11.3 0h1.1a2.25 2.25 0 0 0 2.3-2.25V9.5c0-1.1-.8-2-1.8-2.2M6.3 18H5.3a2.25 2.25 0 0 1-2.3-2.25V9.5c0-1.1.8-2 1.8-2.2m10.5 0a48 48 0 0 0-10.5 0M18 3.4v3.7" />
                        </svg>
                    </span>
                    <span class="sr-only">{{ config('app.name', 'SiRex') }}</span>
                </a>
                <div class="sirex-glass-card flex flex-col gap-6 rounded-[16px] p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
