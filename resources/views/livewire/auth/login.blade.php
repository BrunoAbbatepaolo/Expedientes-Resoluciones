<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Inicie sesión en su cuenta')" :description="__('Ingrese su correo electrónico y contraseña a continuación para iniciar sesión')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <div>
            <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ __('Correo electrónico') }}</label>
            <input wire:model="email" type="email" required autofocus autocomplete="email" placeholder="email@example.com"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
            @error('email') <span class="mt-1 block text-xs text-ipv-magenta">{{ $message }}</span> @enderror
        </div>

        <div>
            <div class="mb-1 flex items-center justify-between">
                <label class="block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ __('Contraseña') }}</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-ipv-blue hover:underline dark:text-ipv-blue-light">
                        {{ __('¿Olvidó su contraseña?') }}
                    </a>
                @endif
            </div>
            <input wire:model="password" type="password" required autocomplete="current-password" placeholder="{{ __('Contraseña') }}"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
            @error('password') <span class="mt-1 block text-xs text-ipv-magenta">{{ $message }}</span> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-ipv-ink/75 dark:text-ipv-ink-dark/80">
            <input wire:model="remember" type="checkbox" class="rounded border-ipv-blue/30 text-ipv-blue focus:ring-ipv-blue/40">
            {{ __('Mantener sesión activa') }}
        </label>

        <button type="submit" wire:loading.attr="disabled"
            class="w-full rounded-lg bg-ipv-blue px-4 py-2.5 text-sm font-semibold text-white transition-transform hover:scale-[1.02] hover:bg-ipv-blue-dark disabled:opacity-60">
            {{ __('Iniciar sesión') }}
        </button>
    </form>
</div>

