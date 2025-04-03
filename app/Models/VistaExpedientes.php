<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VistaExpedientes extends Model
{
    use HasFactory;
    protected $connection = "pgsql_mitiv";
    protected $table = "vista_expedientes";
    protected $fillable = [
        'numero',
        'fecha',
        'asunto',
        'oficina',
        'folio',
        'causante',
        'asunto_id',
    ];
}