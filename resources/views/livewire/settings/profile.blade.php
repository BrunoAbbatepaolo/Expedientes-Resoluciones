<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $nombre = '';

    public string $apellido = '';

    public string $email = '';

    public $profilePhoto = null;

    public $currentPhoto = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->nombre = Auth::user()->nombre;
        $this->apellido = Auth::user()->apellido;
        $this->email = Auth::user()->email;
        $this->currentPhoto = Auth::user()->profile_photo_path;
    }

    /** Reglas de la foto: solo formatos que el navegador muestra y Livewire puede previsualizar. */
    private const REGLAS_FOTO = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024']; // Máximo 1MB

    /**
     * Valida la foto apenas se sube (antes de previsualizarla); si no sirve, la descarta
     * y muestra el error en pantalla en vez de romper la vista previa.
     */
    public function updatedProfilePhoto(): void
    {
        try {
            $this->validateOnly('profilePhoto', ['profilePhoto' => self::REGLAS_FOTO]);
        } catch (ValidationException $e) {
            $this->reset('profilePhoto');

            throw $e;
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'profilePhoto' => self::REGLAS_FOTO,
        ]);

        $user->fill([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'email' => $validated['email'],
        ]);

        if ($this->profilePhoto) {
            // Eliminar la foto anterior si existe
            if ($user->profile_photo_path && Storage::exists('public/'.$user->profile_photo_path)) {
                Storage::delete('public/'.$user->profile_photo_path);
            }

            // Guardar la nueva foto
            $path = $this->profilePhoto->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
            $this->currentPhoto = $path;
            $this->profilePhoto = null;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->nombre);
        $this->dispatch('toast', type: 'success', message: 'Perfil actualizado.');
    }

    /**
     * Delete the user's profile photo.
     */
    public function deleteProfilePhoto(): void
    {
        $user = Auth::user();

        if ($user->profile_photo_path && Storage::exists('public/'.$user->profile_photo_path)) {
            Storage::delete('public/'.$user->profile_photo_path);
        }

        $user->profile_photo_path = null;
        $user->save();

        $this->currentPhoto = null;
        $this->dispatch('profile-photo-deleted');
        $this->dispatch('toast', type: 'success', message: 'Foto eliminada.');
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Perfil')" :subheading="__('Actualizá la información de tu perfil')">
        <form wire:submit="updateProfileInformation" class="w-full space-y-6">
            <!-- Foto de perfil: el círculo es el botón para elegir una nueva -->
            <div class="flex flex-wrap items-center gap-5">
                <div class="group relative size-24 shrink-0">
                    <div class="size-full overflow-hidden rounded-full ring-4 ring-ipv-gold/40 transition group-hover:ring-ipv-gold">
                        @if ($profilePhoto && $profilePhoto->isPreviewable())
                            <img src="{{ $profilePhoto->temporaryUrl() }}" alt="{{ __('Vista previa de foto') }}" class="h-full w-full object-cover">
                        @elseif ($currentPhoto)
                            <img src="{{ Storage::url($currentPhoto) }}" alt="{{ __('Foto actual') }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-ipv-gold text-2xl font-bold text-ipv-gold-ink dark:text-ipv-gold-ink-dark">
                                {{ strtoupper(mb_substr($nombre, 0, 1).mb_substr($apellido, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <input type="file" wire:model="profilePhoto" id="photo-upload" class="hidden" accept="image/jpeg,image/png,image/webp" />
                    <button type="button" onclick="document.getElementById('photo-upload').click();" aria-label="{{ __('Cambiar Foto') }}"
                        class="absolute inset-0 flex items-center justify-center rounded-full bg-[#001428]/55 text-white opacity-0 transition focus-visible:opacity-100 group-hover:opacity-100">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 8a2 2 0 0 1 2-2h1.5l1.2-1.8A1 1 0 0 1 8.5 4h7a1 1 0 0 1 .8.2L17.5 6H19a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8Z" /><circle cx="12" cy="12.5" r="3.5" />
                        </svg>
                    </button>

                    <div wire:loading wire:target="profilePhoto" class="absolute inset-0 flex items-center justify-center rounded-full bg-white/70 dark:bg-black/60">
                        <span class="size-6 animate-spin rounded-full border-2 border-ipv-blue border-r-transparent"></span>
                    </div>
                </div>

                <div class="min-w-0 space-y-2">
                    <p class="text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ __('Foto de Perfil') }}</p>
                    <p class="sirex-ayuda">JPG, PNG o WebP, hasta 1 MB.</p>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="document.getElementById('photo-upload').click();" class="sirex-boton sirex-boton-secundario sirex-boton-chico">{{ __('Cambiar Foto') }}</button>
                        @if ($profilePhoto)
                            <button type="button" wire:click="$set('profilePhoto', null)" class="sirex-boton sirex-boton-secundario sirex-boton-chico">{{ __('Cancelar cambio') }}</button>
                        @elseif ($currentPhoto)
                            <button type="button" wire:click="deleteProfilePhoto" wire:confirm="¿Eliminar tu foto de perfil?" class="sirex-boton sirex-boton-chico text-ipv-magenta hover:bg-ipv-magenta/10 dark:text-ipv-magenta-light">{{ __('Eliminar Foto') }}</button>
                        @endif
                    </div>
                    @error('profilePhoto') <p class="sirex-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Datos de la cuenta que no se editan acá -->
            @if (auth()->user()->legajo || auth()->user()->oficinaAsignada())
                <dl class="grid gap-2 rounded-xl border border-ipv-blue/10 bg-ipv-blue/[0.03] p-3.5 text-sm dark:border-white/10 dark:bg-white/[0.03] sm:grid-cols-2">
                    @if (auth()->user()->legajo)
                        <div><dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Legajo</dt><dd class="font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ auth()->user()->legajo }}</dd></div>
                    @endif
                    @if (auth()->user()->oficinaAsignada())
                        <div><dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Oficina</dt><dd class="font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ auth()->user()->oficinaAsignada()->nombre }}</dd></div>
                    @endif
                </dl>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="perfil-nombre" class="sirex-etiqueta">{{ __('Nombre') }}</label>
                    <input id="perfil-nombre" wire:model="nombre" type="text" required autocomplete="given-name" class="sirex-campo">
                    @error('nombre') <p class="sirex-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="perfil-apellido" class="sirex-etiqueta">{{ __('Apellido') }}</label>
                    <input id="perfil-apellido" wire:model="apellido" type="text" required autocomplete="family-name" class="sirex-campo">
                    @error('apellido') <p class="sirex-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="perfil-email" class="sirex-etiqueta">{{ __('Email') }}</label>
                    <input id="perfil-email" wire:model="email" type="email" required autocomplete="email" class="sirex-campo">
                    @error('email') <p class="sirex-error">{{ $message }}</p> @enderror

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                        <div>
                            <p class="mt-3 text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/75">
                                {{ __('Tu dirección de email no está verificada.') }}

                                <button type="button" wire:click.prevent="resendVerificationNotification"
                                    class="cursor-pointer text-sm text-ipv-blue hover:underline dark:text-ipv-blue-light">
                                    {{ __('Hacé clic acá para reenviar el email de verificación.') }}
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 text-sm font-medium text-ipv-blue dark:text-ipv-blue-light">
                                    {{ __('Se envió un nuevo enlace de verificación a tu dirección de email.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3 border-t border-ipv-blue/10 pt-5 dark:border-white/10">
                <button type="submit" wire:loading.attr="disabled" wire:target="updateProfileInformation" class="sirex-boton sirex-boton-primario">
                    {{ __('Guardar') }}
                </button>
                <x-action-message class="text-emerald-600 dark:text-emerald-300" on="profile-updated">
                    {{ __('Guardado.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
