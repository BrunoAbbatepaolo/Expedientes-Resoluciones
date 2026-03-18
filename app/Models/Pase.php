<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pase extends Model
{
    protected $connection = 'mysql_admin';
    protected $table = 'pases';

    protected $fillable = [
        'expediente_id',
        'oficina_id',
        'oficina_destino_id',
        'fecha',
        'hora',
        'observacion',
        'folio',
        'user_id',
        'importado',
        'firmado',
    ];

    protected $casts = [
        'fecha'     => 'date',
        'importado' => 'boolean',
        'firmado'   => 'boolean',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class);
    }

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    public function oficinaDestino()
    {
        return $this->belongsTo(Oficina::class, 'oficina_destino_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function oficinaOrigen()
    {
        return $this->belongsTo(Oficina::class, 'oficina_origen_id');
    }
}