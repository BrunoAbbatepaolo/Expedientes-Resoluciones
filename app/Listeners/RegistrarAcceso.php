<?php

namespace App\Listeners;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;

/** Deja en la auditoría cada ingreso y cada intento fallido. Nunca se guarda la contraseña. */
class RegistrarAcceso
{
    public function handle(Login|Failed $evento): void
    {
        try {
            if ($evento instanceof Login) {
                $this->escribir((int) $evento->user->getAuthIdentifier(), 'login', ['email' => $evento->user->email ?? null]);

                return;
            }

            $email = (string) ($evento->credentials['email'] ?? '');
            $this->escribir((int) ($evento->user?->getAuthIdentifier() ?? 0), 'login_fallido', ['email' => mb_substr($email, 0, 150)]);
        } catch (\Throwable $e) {
            // Una falla al auditar nunca debe impedir (ni mostrar un error en) el ingreso.
            report($e);
        }
    }

    /** @param  array<string, mixed>  $datos */
    private function escribir(int $usuarioId, string $evento, array $datos): void
    {
        Audit::create([
            'auditable_type' => User::class,
            'auditable_id' => $usuarioId,
            'event' => $evento,
            'old_values' => [],
            'new_values' => $datos,
            'user_id' => $evento === 'login' ? $usuarioId : null,
            'ip_address' => request()->ip(),
        ]);
    }
}
