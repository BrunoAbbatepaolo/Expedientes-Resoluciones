<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'color',
        'assigned_to',
        'created_by',
        'is_completed',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_completed' => 'boolean',
    ];


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
