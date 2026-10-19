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
     * PDF principal: el documento armado con la plantilla (con 'tipo'); si no, el archivo adjunto o el PDF cargado
     * desde "Editar" (en las resoluciones viejas suele ser el firmado); si no, el texto guardado imprimible.
     * Null si no tiene ninguno. Requiere 'archivos' cargado para no hacer N consultas.
     */
    public function urlPdf(): ?string
    {
        return match (true) {
            (bool) $this->tipo => route('resoluciones.imprimir', $this),
            $this->archivos->isNotEmpty() => route('resoluciones.archivo', $this->archivos->first()),
            (bool) $this->pdf => route('resoluciones.pdf', $this),
            (bool) $this->plantilla => route('resoluciones.imprimir', $this),
            default => null,
        };
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(ResolucionArchivo::class);
    }
}
