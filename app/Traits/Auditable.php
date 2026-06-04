<?php

namespace App\Traits;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAudit('CREATE', null, $model->getAttributes());
        });

        static::updating(function ($model) {
            $old = array_intersect_key($model->getOriginal(), $model->getDirty());
            $new = $model->getDirty();
            $model->logAudit('UPDATE', $old, $new);
        });

        static::deleted(function ($model) {
            $model->logAudit('DELETE', $model->getOriginal(), null);
        });
    }

    protected function logAudit(string $action, ?array $old, ?array $new): void
    {
        // Safe password obfuscation in logs
        if ($old && isset($old['password'])) $old['password'] = '******';
        if ($new && isset($new['password'])) $new['password'] = '******';

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action_type' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent()
        ]);
    }
}
