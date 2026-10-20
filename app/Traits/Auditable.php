<?php

namespace App\Traits;

use App\Models\Audit;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Claves que nunca se guardan en la auditoría: las ocultas del modelo, claves y tokens, y las que el modelo
     * excluya en $auditoriaExcluye (p. ej. textos largos).
     */
    private function sinSecretos(array $valores): array
    {
        $secretos = array_merge($this->getHidden(), ['password', 'remember_token'], $this->auditoriaExcluye ?? []);

        return array_diff_key($valores, array_flip($secretos));
    }

    public function recordAudit($event, $old, $new): void
    {
        $old = $this->sinSecretos((array) $old);
        $new = $this->sinSecretos((array) $new);

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
