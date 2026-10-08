<?php

namespace App\Livewire\Rrhh\Concerns;

use App\Services\Rrhh\Alcance;
use App\Services\Rrhh\AlcanceFactory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Comportamiento común de las pantallas RRHH: el alcance del usuario y las consultas
 * al sistema viejo, que pueden fallar sin romper la página.
 */
trait ConAlcance
{
    protected bool $errorFuente = false;

    protected function alcance(): Alcance
    {
        return app(AlcanceFactory::class)->para(auth()->user());
    }

    /** 403 si el usuario no cumple la condición (se evalúa en mount). */
    protected function exigir(bool $condicion): void
    {
        abort_unless($condicion, 403);
    }

    /**
     * Corre una consulta a la fuente de RRHH. Si la base no responde, avisa en pantalla y
     * devuelve $porDefecto; los 403/404 siguen su curso.
     */
    protected function consultar(callable $consulta, mixed $porDefecto = null): mixed
    {
        try {
            return $consulta();
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (ModelNotFoundException|HttpExceptionInterface|ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->errorFuente = true;

            return $porDefecto;
        }
    }
}
