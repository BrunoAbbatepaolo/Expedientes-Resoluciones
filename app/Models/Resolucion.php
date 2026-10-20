<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Resoluciones\Documento;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resolucion extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    /** Columnas grandes que no se guardan en la auditoría (el historial muestra solo los datos de la lista). */
    protected array $auditoriaExcluye = ['plantilla', 'documento_html', 'datos'];

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

    // documento_html no es asignable en masa: se escribe solo desde regenerarDocumento() (HTML armado en el servidor).

    /** datos: ['campos' => [...], 'articulos_quitados' => [...]] de la "Plantilla completa" (ver Documento). */
    protected function casts(): array
    {
        return [
            'datos' => 'array',
        ];
    }

    /**
     * Las que ve el usuario: las de su oficina y las viejas sin oficina (ver implementacion_futuro.md 2.1),
     * que se muestran a todos hasta que se les asigne una.
     */
    public function scopeVisiblesPara(Builder $query, User $usuario): Builder
    {
        $oficinaId = $usuario->oficinaAsignadaId() ?? $usuario->oficinaIdPara('resolucion_ver');

        return $query->where(function (Builder $q) use ($oficinaId) {
            $q->whereNull('oficina_id');

            if ($oficinaId) {
                $q->orWhere('oficina_id', $oficinaId);
            }
        });
    }

    /** Filtro de la lista: $tipo es un tipo de Tipos::TODOS o 'otras' (sin tipo); $buscar, texto libre. */
    public function scopeFiltrar(Builder $query, string $buscar, string $tipo): Builder
    {
        if ($tipo === 'otras') {
            $query->whereNull('tipo');
        } elseif ($tipo !== '') {
            $query->where('tipo', $tipo);
        }

        if (trim($buscar) !== '') {
            $termino = '%'.addcslashes(trim($buscar), '%_\\').'%';
            $query->where(fn (Builder $q) => $q->where('numero_exp', 'like', $termino)
                ->orWhere('numero_resolucion', 'like', $termino)
                ->orWhere('cod_barrio', 'like', $termino)
                ->orWhere('cod_casa', 'like', $termino));
        }

        return $query;
    }

    /** ¿Se puede usar como base de una nueva? Solo las de "Plantilla completa" (tienen sus datos guardados). */
    public function esPlantillaCompleta(): bool
    {
        /** @var array{campos?: array<string, mixed>}|null $datos cast 'array' */
        $datos = $this->datos;

        return $this->tipo && is_array($datos) && isset($datos['campos']);
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

    /**
     * Vuelve a armar y guardar el documento fijo de una resolución de "Plantilla completa" (tras editar sus datos).
     * Las de tipo Personalizado y las cargadas como archivo no tienen: su texto ya es el definitivo.
     */
    public function regenerarDocumento(): void
    {
        /** @var array{campos?: array<string, mixed>, articulos_quitados?: list<int>, personalizado?: bool}|null $datos */
        $datos = $this->datos;
        if (! $this->tipo || ! is_array($datos) || ! empty($datos['personalizado']) || ! Documento::existe($this->tipo)) {
            return;
        }

        $this->forceFill(['documento_html' => Documento::html(
            $this->tipo,
            (array) ($datos['campos'] ?? []),
            array_map('intval', $datos['articulos_quitados'] ?? []),
        )])->save();
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(ResolucionArchivo::class);
    }
}
