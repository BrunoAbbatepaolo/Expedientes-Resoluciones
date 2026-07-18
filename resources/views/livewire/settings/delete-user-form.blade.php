<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <h3 class="text-base font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ __('Eliminar cuenta') }}</h3>
        <p class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ __('Eliminá tu cuenta y todos sus recursos') }}</p>
    </div>

    <div x-data="{ show: {{ $errors->isNotEmpty() ? 'true' : 'false' }} }"
        x-on:open-modal.window="if ($event.detail[0] === 'confirm-user-deletion') show = true"
        x-on:close-modal.window="if ($event.detail[0] === 'confirm-user-deletion') show = false">

        <button type="button" @click="show = true"
            class="rounded-lg bg-ipv-magenta px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
            {{ __('Eliminar cuenta') }}
        </button>

        <div x-show="show" x-cloak x-on:keydown.escape.window="show = false"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-[#001428]/50 p-4 backdrop-blur-sm"
            x-on:click.self="show = false">
            <div class="w-full max-w-lg rounded-2xl border border-ipv-blue/20 bg-white/95 p-6 shadow-2xl backdrop-blur-2xl backdrop-saturate-150 dark:border-white/10 dark:bg-[#141c26]/95">
                <form wire:submit="deleteUser" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ __('¿Estás seguro de que querés eliminar tu cuenta?') }}</h3>

                        <p class="mt-1 text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/75">
                            {{ __('Una vez eliminada tu cuenta, todos sus recursos y datos se borrarán permanentemente. Ingresá tu contraseña para confirmar que querés eliminar tu cuenta de forma definitiva.') }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ __('Contraseña') }}</label>
                        <input wire:model="password" type="password"
                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                        @error('password') <span class="mt-1 block text-xs text-ipv-magenta">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="show = false"
                            class="rounded-lg border border-ipv-blue/20 px-4 py-2 text-sm font-semibold text-ipv-ink/80 hover:bg-black/[0.03] dark:border-white/15 dark:text-ipv-ink-dark/85 dark:hover:bg-white/5">
                            {{ __('Cancelar') }}
                        </button>

                        <button type="submit" class="rounded-lg bg-ipv-magenta px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                            {{ __('Eliminar cuenta') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
