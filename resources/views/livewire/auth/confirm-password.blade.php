<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Confirmar contraseña')"
        :description="__('Esta es un área segura de la aplicación. Confirme su contraseña antes de continuar.')"
    />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="confirmPassword" class="flex flex-col gap-6">
        <div>
            <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ __('Contraseña') }}</label>
            <input wire:model="password" type="password" required autocomplete="new-password" placeholder="{{ __('Contraseña') }}"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
            @error('password') <span class="mt-1 block text-xs text-ipv-magenta">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full rounded-lg bg-ipv-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-ipv-blue-dark">
            {{ __('Confirmar') }}
        </button>
    </form>
</div>
