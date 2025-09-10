<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
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
                Rule::unique(User::class)->ignore($user->id)
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
            if ($user->profile_photo_path && Storage::exists('public/' . $user->profile_photo_path)) {
                Storage::delete('public/' . $user->profile_photo_path);
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
        
        if ($user->profile_photo_path && Storage::exists('public/' . $user->profile_photo_path)) {
            Storage::delete('public/' . $user->profile_photo_path);
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

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your profile information')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <!-- Sección de foto de perfil -->
            <div class="mb-6">
                <flux:label>{{ __('Foto de Perfil') }}</flux:label>
                
                <div class="mt-4 flex items-center space-x-6">
                    <div class="relative h-20 w-20 overflow-hidden rounded-full">
                        @if ($profilePhoto)
                            <img src="{{ $profilePhoto->temporaryUrl() }}" alt="{{ __('Vista previa de foto') }}" class="h-full w-full object-cover">
                        @elseif ($currentPhoto)
                            <img src="{{ Storage::url($currentPhoto) }}" alt="{{ __('Foto actual') }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center rounded-full bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                {{ substr($nombre, 0, 1) . substr($apellido, 0, 1) }}
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
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            {{ __('Cambiar Foto') }}
                        </button>
                        
                        @if ($currentPhoto || $profilePhoto)
                            <flux:button wire:click="deleteProfilePhoto" size="sm" color="danger">
                                {{ __('Eliminar Foto') }}
                            </flux:button>
                        @endif
                    </div>
                                    
                @error('profilePhoto') 
                    <flux:text class="mt-2 text-sm text-red-600">{{ $message }}</flux:text>
                @enderror
                
                @if ($profilePhoto)
                    <div class="mt-2">
                        <flux:button wire:click="$set('profilePhoto', null)" size="sm" color="secondary">
                            {{ __('Cancelar cambio') }}
                        </flux:button>
                    </div>
                @endif
            </div>

            <!-- Campos existentes -->
            <flux:input wire:model="nombre" :label="__('Nombre')" type="text" required autofocus autocomplete="nombre" />
            <flux:input wire:model="apellido" :label="__('Apellido')" type="text" required autofocus autocomplete="apellido" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
