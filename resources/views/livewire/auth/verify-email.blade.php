<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="mt-4 flex flex-col gap-6">
    <p class="text-center text-sm text-ipv-ink/75 dark:text-ipv-ink-dark/80">
        {{ __('Por favor verifique su dirección de email haciendo clic en el enlace que le enviamos.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <p class="text-center text-sm font-medium text-ipv-blue dark:text-ipv-blue-light">
            {{ __('Se envió un nuevo enlace de verificación al email que registró.') }}
        </p>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <button wire:click="sendVerification" type="button"
            class="w-full rounded-lg bg-ipv-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-ipv-blue-dark">
            {{ __('Reenviar email de verificación') }}
        </button>

        <button wire:click="logout" type="button" class="cursor-pointer text-sm text-ipv-blue hover:underline dark:text-ipv-blue-light">
            {{ __('Cerrar sesión') }}
        </button>
    </div>
</div>
