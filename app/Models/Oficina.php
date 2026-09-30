<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Oficina extends Model
{
    use HasFactory;

    protected $connection = 'mysql_admin';

    protected $table = 'oficinas';

    protected $fillable = [
        'cod_area',
        'nombre',
        'codigo',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'cod_area', 'codigo');
    }

    public function expedientes(): HasMany
    {
        return $this->hasMany(Expediente::class, 'cod_oficina', 'codigo')
            ->where('cod_area', $this->cod_area);
    }
}
