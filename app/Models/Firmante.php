<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Persona que firma resoluciones; se elige en la "Plantilla completa". La resolución guarda una copia
 * del nombre y cargo (datos.firma_N_*), así un PDF viejo no cambia si después se edita el firmante.
 */
class Firmante extends Model
{
    use Auditable;

    protected $table = 'firmantes';

    protected $fillable = ['nombre', 'cargo', 'dependencia', 'activo', 'orden'];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'orden' => 'integer'];
    }

    /** @param  Builder<self>  $query */
    public function scopeParaElegir(Builder $query): void
    {
        $query->where('activo', true)->orderBy('orden')->orderBy('nombre');
    }

    /** @return array{nombre: string, cargo: string, dependencia: string} */
    public function paraResolucion(): array
    {
        return ['nombre' => $this->nombre, 'cargo' => $this->cargo, 'dependencia' => (string) $this->dependencia];
    }
}
