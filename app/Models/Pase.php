<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pase extends Model
{
    use Auditable;
    use HasFactory;

    protected $connection = 'mysql_admin';

    protected $table = 'pases';

    protected $fillable = [
        'expediente_id',
        'oficina_id',
        'oficina_origen_id',
        'oficina_destino_id',
        'fecha',
        'hora',
        'observacion',
        'folio',
        'user_id',
        'importado',
        'firmado',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'importado' => 'boolean',
        'firmado' => 'boolean',
    ];

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class);
    }

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    public function oficinaDestino(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_destino_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function oficinaOrigen(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_origen_id');
    }
}
