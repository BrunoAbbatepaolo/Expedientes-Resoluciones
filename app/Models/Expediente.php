<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expediente extends Model
{
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
}
