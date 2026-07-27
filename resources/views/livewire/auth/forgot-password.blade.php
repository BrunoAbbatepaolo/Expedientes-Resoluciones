<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('¿Olvidó su contraseña?')" :description="__('Ingrese su email para recibir un enlace de restablecimiento')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <div>
            <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ __('Correo electrónico') }}</label>
            <input wire:model="email" type="email" required autofocus placeholder="email@example.com"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
            @error('email') <span class="mt-1 block text-xs text-ipv-magenta">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full rounded-lg bg-ipv-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-ipv-blue-dark">
            {{ __('Enviar enlace de restablecimiento') }}
        </button>
    </form>

    <div class="space-x-1 text-center text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
        {{ __('O, volvé a') }}
        <a href="{{ route('login') }}" wire:navigate class="text-ipv-blue hover:underline dark:text-ipv-blue-light">{{ __('iniciar sesión') }}</a>
    </div>
</div>
