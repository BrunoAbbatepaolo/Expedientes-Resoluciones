<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $fillable = ['user_id', 'nombre'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
