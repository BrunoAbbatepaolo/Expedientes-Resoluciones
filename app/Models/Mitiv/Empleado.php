<?php

namespace App\Models\Mitiv;

use App\Enums\NivelDatoRrhh;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Empleado del IPV (fuente: sistema viejo).
 *
 * La tabla real tiene datos muy sensibles (claves, CUIL, salud, cuenta bancaria):
 * el scope global limita el SELECT a estas columnas, así nunca salen de la base.
 *
 * @property int $id
 * @property int $legajo
 * @property string $apellido
 * @property string $nombre
 * @property string|null $email
 * @property int $oficina_id
 * @property int $situacion_laboral_id
 * @property bool $habilitado
 * @property bool|null $espasivo
 *
 * Columnas de RRHH (solo presentes si se pidieron con conNivel()):
 * @property int|string|null $card_id
 * @property string|null $funcion
 * @property int|null $categoria_titular_id
 * @property int|null $categoria_interina_id
 * @property Carbon|null $fecha_ingreso
 * @property string|null $horario
 * @property string|null $numero_documento
 * @property string|null $cuil
 * @property string|null $domicilio
 * @property string|null $telefono
 * @property string|null $telefono_celular
 * @property Carbon|null $fecha_nacimiento
 * @property string|null $lugar_nacimiento
 * @property string|null $sexo
 * @property string|null $nacionalidad
 * @property string|null $nivel_estudio
 * @property string|null $matricula_profesional
 * @property int|string|null $numero_obra_social
 * @property string|null $otra_obra_social
 * @property int|string|null $numero_ipss
 * @property string|null $aseguradora
 * @property string|null $otra_aseguradora
 * @property int|string|null $numero_poliza
 * @property string|null $emergencia
 * @property string|null $telefono_emergencia
 * @property string|null $resolucion
 * @property string|null $fecha_resolucion
 * @property string|null $fecha_contratado
 * @property string|null $res_dec_contrato
 * @property string|null $observacion
 * @property string|null $conyuge_nombre
 * @property string|null $conyuge_cuil
 * @property bool|null $es_discapacitado
 * @property string|null $tipo_discapacidad
 * @property string|null $grado_discapacidad
 * @property string|null $vigencia_certificado_discapacidad
 * @property string|null $enfermedad
 * @property string|null $tratamiento
 * @property string|null $nro_cuenta_bancaria
 * @property int|null $anio_reconocimiento_nacional
 * @property int|null $mes_reconocimiento_nacional
 * @property int|null $dia_reconocimiento_nacional
 * @property int|null $anio_reconocimiento_provincial
 * @property int|null $mes_reconocimiento_provincial
 * @property int|null $dia_reconocimiento_provincial
 * @property int|null $anio_reconocimiento_municipal
 * @property int|null $mes_reconocimiento_municipal
 * @property int|null $dia_reconocimiento_municipal
 * @property-read OficinaRrhh|null $oficinaRrhh
 * @property-read SituacionLaboral|null $situacionLaboral
 * @property-read Categoria|null $categoriaTitular
 * @property-read Categoria|null $categoriaInterina
 */
class Empleado extends MitivModel
{
    protected $table = 'empleados';

    public const COLUMNAS_PUBLICAS = [
        'id', 'legajo', 'apellido', 'nombre', 'email', 'oficina_id',
        'situacion_laboral_id', 'habilitado', 'espasivo',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('columnas_publicas', function (Builder $query) {
            $query->select(array_map(fn ($c) => 'empleados.'.$c, self::COLUMNAS_PUBLICAS));
        });
    }

    protected function casts(): array
    {
        return [
            'habilitado' => 'boolean',
            'espasivo' => 'boolean',
            'fecha_ingreso' => 'date',
            'fecha_nacimiento' => 'date',
            'es_discapacitado' => 'boolean',
        ];
    }

    /** Activos = habilitados y no pasivos (jubilados, bajas). */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('empleados.habilitado', true)
            ->where(fn ($q) => $q->where('empleados.espasivo', false)->orWhereNull('empleados.espasivo'));
    }

    /**
     * Reemplaza las columnas públicas por las de un nivel del legajo. Solo debe usarse
     * desde los servicios de RRHH, que deciden el nivel según el Alcance del usuario.
     */
    public function scopeConNivel(Builder $query, NivelDatoRrhh $nivel): Builder
    {
        return $query->withoutGlobalScope('columnas_publicas')
            ->select(array_map(fn ($c) => 'empleados.'.$c, $nivel->columnas()));
    }

    public function situacionLaboral(): BelongsTo
    {
        return $this->belongsTo(SituacionLaboral::class, 'situacion_laboral_id');
    }

    /** Oficina del catálogo de RRHH (mitivdb.oficinas). */
    public function oficinaRrhh(): BelongsTo
    {
        return $this->belongsTo(OficinaRrhh::class, 'oficina_id');
    }

    public function categoriaTitular(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_titular_id');
    }

    public function categoriaInterina(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_interina_id');
    }

    public function oficinasExpediente(): BelongsToMany
    {
        return $this->belongsToMany(OficinaExpediente::class, 'empleado_expediente_oficinas', 'empleado_id', 'expediente_oficina_id')
            ->wherePivot('habilitado', true);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->apellido.', '.$this->nombre, ', ');
    }
}
