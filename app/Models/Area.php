<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    protected $connection = "mysql_legui";
    protected $table = "areas";
    protected $fillable = [
        'nombre',
        'codigo',
    ];
    public function oficinas()
    {
        return $this->hasMany(Oficina::class, 'codigo', 'cod_area');
    }
}
