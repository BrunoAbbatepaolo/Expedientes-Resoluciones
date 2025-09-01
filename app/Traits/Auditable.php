<?php

namespace App\Traits;

use App\Models\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->recordAudit('created', [], $model->getAttributes());
        });

        static::updating(function ($model) {
            $model->recordAudit('updated', $model->getOriginal(), $model->getDirty());
        });

        static::deleted(function ($model) {
            $model->recordAudit('deleted', $model->getOriginal(), []);
        });
    }

    public function recordAudit($event, $old, $new): void
    {
        Audit::create([
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'event' => $event,
            'old_values' => $old,
            'new_values' => $new,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    public function audits()
    {
        return $this->morphMany(Audit::class, 'auditable');
    }
}
