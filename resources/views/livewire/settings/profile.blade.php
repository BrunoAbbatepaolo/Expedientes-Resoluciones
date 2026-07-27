<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
            'profilePhoto' => ['nullable', 'image', 'max:1024'], // Máximo 1MB
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
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <!-- Sección de foto de perfil -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ __('Foto de Perfil') }}</label>

                <div class="mt-4 flex items-center space-x-6">
                    <div class="relative h-20 w-20 overflow-hidden rounded-full">
                        @if ($profilePhoto)
                            <img src="{{ $profilePhoto->temporaryUrl() }}" alt="{{ __('Vista previa de foto') }}" class="h-full w-full object-cover">
                        @elseif ($currentPhoto)
                            <img src="{{ Storage::url($currentPhoto) }}" alt="{{ __('Foto actual') }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center rounded-full bg-ipv-gold text-ipv-gold-ink dark:text-ipv-gold-ink-dark">
                                {{ substr($nombre, 0, 1).substr($apellido, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col space-y-2 p-2">
                        <input
                            type="file"
                            wire:model="profilePhoto"
                            id="photo-upload"
                            class="hidden"
                            accept="image/*"
                        />
                        <button
                            type="button"
                            onclick="document.getElementById('photo-upload').click();"
                            class="inline-flex items-center rounded-md border border-ipv-blue/20 bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-ipv-ink/80 transition hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]"
                        >
                            {{ __('Cambiar Foto') }}
                        </button>

                        @if ($currentPhoto || $profilePhoto)
                            <button type="button" wire:click="deleteProfilePhoto"
                                class="rounded-lg bg-ipv-magenta px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90">
                                {{ __('Eliminar Foto') }}
                            </button>
                        @endif
                    </div>

                @error('profilePhoto')
                    <span class="mt-2 block text-sm text-ipv-magenta">{{ $message }}</span>
                @enderror

                @if ($profilePhoto)
                    <div class="mt-2">
                        <button type="button" wire:click="$set('profilePhoto', null)"
                            class="rounded-lg border border-ipv-blue/20 px-3 py-1.5 text-xs font-semibold text-ipv-ink/75 hover:bg-black/[0.03] dark:border-white/15 dark:text-ipv-ink-dark/80 dark:hover:bg-white/5">
                            {{ __('Cancelar cambio') }}
                        </button>
                    </div>
                @endif
            </div>

            <!-- Campos existentes -->
            <div>
                <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ __('Nombre') }}</label>
                <input wire:model="nombre" type="text" required autofocus autocomplete="nombre"
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                @error('nombre') <span class="mt-1 block text-xs text-ipv-magenta">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ __('Apellido') }}</label>
                <input wire:model="apellido" type="text" required autocomplete="apellido"
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                @error('apellido') <span class="mt-1 block text-xs text-ipv-magenta">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ __('Email') }}</label>
                <input wire:model="email" type="email" required autocomplete="email"
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                @error('email') <span class="mt-1 block text-xs text-ipv-magenta">{{ $message }}</span> @enderror

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                    <div>
                        <p class="mt-4 text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/75">
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

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <button type="submit" class="w-full rounded-lg bg-ipv-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-ipv-blue-dark">
                        {{ __('Guardar') }}
                    </button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Guardado.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
