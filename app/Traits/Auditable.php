<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn($model) => static::writeAudit('created', $model, [], $model->getAttributes()));
        static::updated(fn($model) => static::writeAudit('updated', $model, $model->getOriginal(), $model->getChanges()));
        static::deleted(fn($model) => static::writeAudit('deleted', $model, $model->getAttributes(), []));

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))) {
            static::restored(fn($model) => static::writeAudit('restored', $model, [], $model->getAttributes()));
        }
    }

    protected static function writeAudit(string $action, $model, array $old, array $new): void
    {
        // Jangan audit tabel audit_logs itu sendiri
        if ($model instanceof AuditLog) {
            return;
        }

        $user   = auth()->user();
        $except = ['password', 'remember_token', 'updated_at'];

        AuditLog::create([
            'user_id'        => $user?->id,
            'user_name'      => $user?->name ?? 'System',
            'action'         => $action,
            'auditable_type' => get_class($model),
            'auditable_id'   => (string) $model->getKey(),
            'old_values'     => $old ? array_diff_key($old, array_flip($except)) : null,
            'new_values'     => $new ? array_diff_key($new, array_flip($except)) : null,
            'ip_address'     => request()?->ip(),
            'url'            => request()?->fullUrl(),
            'created_at'     => now(),
        ]);
    }
}
