<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogObserver
{
    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logEvent($model, 'created');
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        $oldValues = $model->getOriginal();
        $newValues = $model->getChanges();

        // Remove timestamps from old values for cleaner logs
        unset($oldValues['updated_at']);

        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_id' => $model->getKey(),
            'auditable_type' => get_class($model),
            'event' => 'updated',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logEvent($model, 'deleted', $model->getAttributes());
    }

    /**
     * Handle the model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->logEvent($model, 'restored');
    }

    /**
     * Log an audit event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $event
     * @param  array|null  $oldValues
     * @return void
     */
    private function logEvent(Model $model, string $event, ?array $oldValues = null): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_id' => $model->getKey(),
            'auditable_type' => get_class($model),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $event === 'deleted' ? null : $model->getAttributes(),
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}

