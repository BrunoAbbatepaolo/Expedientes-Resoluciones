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

    public function archivos(): HasMany
    {
        return $this->hasMany(ResolucionArchivo::class);
    }
}
