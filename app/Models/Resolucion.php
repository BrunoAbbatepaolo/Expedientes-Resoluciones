<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resolucion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'resoluciones';

    public $timestamps = true;

    protected $fillable = [
        'oficina_id',
        'numero_exp',
        'numero_resolucion',
        'plantilla',
        'fecha',
        'fecha_ingreso',
        'cod_barrio',
        'cod_casa',
        'pdf',
        'tipo',
        'datos',
    ];

    /** datos: ['campos' => [...], 'articulos_quitados' => [...]] de la "Plantilla completa" (ver Documento). */
    protected function casts(): array
    {
        return [
            'datos' => 'array',
        ];
    }

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(VistaExpedientes::class, 'numero_exp', 'numero');
    }

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    /**
     * PDF principal: el documento armado en SiRex (imprimible) o, si no hay, el primer archivo adjunto
     * o el PDF cargado desde "Editar". Null si no tiene ninguno. Requiere 'archivos' cargado para no hacer N consultas.
     */
    public function urlPdf(): ?string
    {
        if ($this->plantilla) {
            return route('resoluciones.imprimir', $this);
        }
        if ($this->archivos->isNotEmpty()) {
            return route('resoluciones.archivo', $this->archivos->first());
        }

        return $this->pdf ? route('resoluciones.pdf', $this) : null;
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(ResolucionArchivo::class);
    }
}
