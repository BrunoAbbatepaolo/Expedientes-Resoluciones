<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oficina extends Model
{
    use HasFactory;
    protected $connection = "mysql_legui";
    protected $table = "oficinas";
    protected $fillable = [
        'cod_area',
        'nombre',
        'codigo',
    ];
    public function area()
    {
        return $this->belongsTo(Area::class, 'cod_area', 'codigo');
    }
    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'cod_oficina', 'codigo')
            ->where('cod_area', $this->cod_area);
    }
}
