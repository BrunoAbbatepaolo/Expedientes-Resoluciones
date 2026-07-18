<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    public string $password = '';

    public string $password_confirmation = '';

    public function guardar(): void
    {
        $validated = $this->validate([
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Auth::user();
        $user->password = $validated['password'];
        $user->require_password_change = false;
        $user->save();

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Actualice su contraseña')"
        :description="__('Por seguridad, debe establecer una nueva contraseña antes de continuar')"
    />

    <form wire:submit="guardar" class="flex flex-col gap-6">
        <div>
            <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ __('Nueva contraseña') }}</label>
            <input wire:model="password" type="password" required autofocus autocomplete="new-password"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
            @error('password') <span class="mt-1 block text-xs text-ipv-magenta">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ __('Confirmar contraseña') }}</label>
            <input wire:model="password_confirmation" type="password" required autocomplete="new-password"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
        </div>

        <button type="submit" class="w-full rounded-lg bg-ipv-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-ipv-blue-dark">
            {{ __('Guardar contraseña') }}
        </button>
    </form>
</div>
