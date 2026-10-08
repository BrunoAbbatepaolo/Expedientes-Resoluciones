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
            class="sirex-modal-fondo" x-on:click.self="show = false">
            <form wire:submit="deleteUser" class="sirex-modal sirex-modal-peligro" style="max-width: 32rem;" role="alertdialog" aria-modal="true" aria-labelledby="titulo-eliminar-cuenta">
                <div class="sirex-modal-cabeza">
                    <div class="sirex-modal-icono sirex-modal-icono-peligro">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="titulo-eliminar-cuenta" class="sirex-modal-titulo">{{ __('¿Estás seguro de que querés eliminar tu cuenta?') }}</h3>
                        <p class="sirex-modal-subtitulo">
                            {{ __('Una vez eliminada tu cuenta, todos sus recursos y datos se borrarán permanentemente. Ingresá tu contraseña para confirmar que querés eliminar tu cuenta de forma definitiva.') }}
                        </p>
                    </div>
                </div>

                <div class="sirex-modal-cuerpo">
                    <div>
                        <label class="sirex-etiqueta">{{ __('Contraseña') }}</label>
                        <input wire:model="password" type="password" class="sirex-campo">
                        @error('password') <p class="sirex-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="sirex-modal-pie">
                    <button type="button" @click="show = false" class="sirex-boton sirex-boton-secundario">{{ __('Cancelar') }}</button>
                    <button type="submit" class="sirex-boton sirex-boton-peligro">{{ __('Eliminar cuenta') }}</button>
                </div>
            </form>
        </div>
    </div>
</section>
