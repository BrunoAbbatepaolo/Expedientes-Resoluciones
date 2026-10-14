<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        \App\Models\Audit::registrar(Auth::user(), 'clave_cambiada');

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
        $this->dispatch('toast', type: 'success', message: 'Contraseña actualizada.');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Actualizar contraseña')" :subheading="__('Asegurate de usar una contraseña larga y aleatoria para mantener la seguridad de tu cuenta')">
        {{-- El medidor y la lista de requisitos son solo una guía en el navegador: las reglas reales las valida el servidor. --}}
        <form wire:submit="updatePassword" class="space-y-5"
            x-data="{
                nueva: '', conf: '',
                reglas() {
                    return [
                        { texto: '8 caracteres o más', ok: this.nueva.length >= 8 },
                        { texto: 'Mayúsculas y minúsculas', ok: /[a-z]/.test(this.nueva) && /[A-Z]/.test(this.nueva) },
                        { texto: 'Al menos un número', ok: /\d/.test(this.nueva) },
                        { texto: 'Un símbolo (recomendado)', ok: /[^A-Za-z0-9]/.test(this.nueva) },
                    ];
                },
                get puntaje() {
                    if (! this.nueva) return 0;
                    if (this.nueva.length < 10) return 1;
                    return Math.max(1, this.reglas().filter(r => r.ok).length);
                },
                get nivel() { return ['', 'Débil', 'Regular', 'Buena', 'Fuerte'][this.puntaje]; },
                get color() { return ['', '#cf2972', '#d97706', '#0284c7', '#059669'][this.puntaje]; },
            }"
            x-on:submit="setTimeout(() => { nueva = ''; conf = ''; }, 80)">

            {{-- Campo de usuario oculto: los gestores de contraseñas lo usan para asociar la clave nueva a la cuenta. --}}
            <input type="text" value="{{ auth()->user()->email }}" autocomplete="username" class="hidden" tabindex="-1" aria-hidden="true" readonly>

            <div x-data="{ ver: false }">
                <label for="pw-actual" class="sirex-etiqueta">{{ __('Contraseña actual') }}</label>
                <div class="relative">
                    <input id="pw-actual" wire:model="current_password" :type="ver ? 'text' : 'password'" required autocomplete="current-password" class="sirex-campo pr-10">
                    <button type="button" @click="ver = ! ver" :aria-label="ver ? 'Ocultar contraseña' : 'Mostrar contraseña'" class="sirex-modal-cerrar absolute right-1.5 top-1/2 !m-0 -translate-y-1/2">
                        <x-ojo />
                    </button>
                </div>
                @error('current_password') <p class="sirex-error">{{ $message }}</p> @enderror
            </div>

            <div x-data="{ ver: false }">
                <label for="pw-nueva" class="sirex-etiqueta">{{ __('Nueva contraseña') }}</label>
                <div class="relative">
                    <input id="pw-nueva" wire:model="password" x-model="nueva" :type="ver ? 'text' : 'password'" required autocomplete="new-password" class="sirex-campo pr-10">
                    <button type="button" @click="ver = ! ver" :aria-label="ver ? 'Ocultar contraseña' : 'Mostrar contraseña'" class="sirex-modal-cerrar absolute right-1.5 top-1/2 !m-0 -translate-y-1/2">
                        <x-ojo />
                    </button>
                </div>
                @error('password') <p class="sirex-error">{{ $message }}</p> @enderror

                <div x-show="nueva.length > 0" x-collapse x-cloak class="mt-3 space-y-2.5">
                    <div class="flex items-center gap-3">
                        <div class="flex flex-1 gap-1" aria-hidden="true">
                            <template x-for="n in 4" :key="n">
                                <span class="h-1.5 flex-1 rounded-full bg-black/10 transition-colors duration-300 dark:bg-white/15" :style="n <= puntaje ? `background:${color}` : ''"></span>
                            </template>
                        </div>
                        <span class="w-14 text-right text-xs font-semibold" :style="`color:${color}`" x-text="nivel" aria-live="polite"></span>
                    </div>
                    <ul class="grid gap-1 sm:grid-cols-2">
                        <template x-for="r in reglas()" :key="r.texto">
                            <li class="flex items-center gap-1.5 text-xs transition-colors" :class="r.ok ? 'text-emerald-600 dark:text-emerald-300' : 'text-ipv-ink/50 dark:text-ipv-ink-dark/50'">
                                <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path x-show="r.ok" d="M5 13l4 4L19 7" />
                                    <circle x-show="! r.ok" cx="12" cy="12" r="3" fill="currentColor" stroke="none" />
                                </svg>
                                <span x-text="r.texto"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            <div x-data="{ ver: false }">
                <label for="pw-confirmar" class="sirex-etiqueta">{{ __('Confirmar contraseña') }}</label>
                <div class="relative">
                    <input id="pw-confirmar" wire:model="password_confirmation" x-model="conf" :type="ver ? 'text' : 'password'" required autocomplete="new-password" class="sirex-campo pr-10">
                    <button type="button" @click="ver = ! ver" :aria-label="ver ? 'Ocultar contraseña' : 'Mostrar contraseña'" class="sirex-modal-cerrar absolute right-1.5 top-1/2 !m-0 -translate-y-1/2">
                        <x-ojo />
                    </button>
                </div>
                <p x-show="conf.length > 0" x-cloak class="mt-1 text-xs font-medium" :class="conf === nueva ? 'text-emerald-600 dark:text-emerald-300' : 'text-ipv-magenta dark:text-ipv-magenta-light'"
                    x-text="conf === nueva ? 'Las contraseñas coinciden' : 'Las contraseñas no coinciden'" aria-live="polite"></p>
            </div>

            <div class="flex items-center gap-3 border-t border-ipv-blue/10 pt-5 dark:border-white/10">
                <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword" class="sirex-boton sirex-boton-primario">
                    {{ __('Guardar') }}
                </button>
                <x-action-message class="text-emerald-600 dark:text-emerald-300" on="password-updated">
                    {{ __('Guardado.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
