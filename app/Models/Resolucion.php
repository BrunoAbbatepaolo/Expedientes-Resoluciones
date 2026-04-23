<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resolucion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'resoluciones';
    public $timestamps = true;

    protected $fillable = [
        'numero_exp',
        'numero_resolucion',
        'fecha',
        'fecha_ingreso',
        'cod_barrio',
        'cod_casa',
        'pdf',
    ];

    public function expediente()
    {
        return $this->belongsTo(VistaExpedientes::class, 'numero_exp', 'numero');
    }
}
