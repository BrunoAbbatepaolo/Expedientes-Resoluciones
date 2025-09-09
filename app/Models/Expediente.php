<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Expediente extends Model
{
    use Auditable;
    use HasFactory;
    use SoftDeletes;
    protected $connection = 'mysql_admin'; // Usar la conexión nueva
    protected $table = 'expedientes';
    protected $fillable = [
        'num_exp',
        'folio',
        'causante',
        'asunto',
        'fecha_ingreso',
        'ofi_salida',
        'cod_area',
        'cod_oficina',
        'fecha_salida',
        'oficina_id',
    ];

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'cod_oficina', 'codigo')
            ->where('cod_area', $this->cod_area); // Asegura que también coincida el área
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'cod_area', 'codigo');
    }
    public function scopeDeOficina(Builder $query, int $oficinaId): Builder
    {
        return $query->where('oficina_id', $oficinaId);
    }
    public function oficinaById()
    {
        return $this->belongsTo(Oficina::class, 'oficina_id', 'id');
    }
}
