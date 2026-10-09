<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad para todas las respuestas web.
 *
 * No incluye una Content-Security-Policy con script-src: Livewire y Alpine usan scripts y
 * expresiones inline, y una política estricta rompería la aplicación. Las directivas que sí se
 * ponen (frame-ancestors, base-uri, form-action, object-src) no necesitan permitir nada inline.
 */
class CabecerasDeSeguridad
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $cabeceras = [
            // Nadie puede incrustar el sistema en otro sitio (clickjacking); los PDF propios en iframe sí.
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Content-Security-Policy' => "frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'",
        ];

        // Solo con HTTPS: avisar al navegador que no vuelva a entrar por HTTP.
        if ($request->isSecure()) {
            $cabeceras['Strict-Transport-Security'] = 'max-age=15552000';
        }

        foreach ($cabeceras as $nombre => $valor) {
            if (! $response->headers->has($nombre)) {
                $response->headers->set($nombre, $valor);
            }
        }

        return $response;
    }
}
