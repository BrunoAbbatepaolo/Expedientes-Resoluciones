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
        <flux:input
            wire:model="password"
            :label="__('Nueva contraseña')"
            type="password"
            required
            autofocus
            autocomplete="new-password"
        />

        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirmar contraseña')"
            type="password"
            required
            autocomplete="new-password"
        />

        <flux:button type="submit" variant="primary" class="w-full">
            {{ __('Guardar contraseña') }}
        </flux:button>
    </form>
</div>
