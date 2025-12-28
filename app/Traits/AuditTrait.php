<?php

namespace App\Traits;

trait AuditTrait
{
    /**
     * Boot the trait and register event listeners.
     */
    public static function bootAuditTrait(): void
    {
        static::creating(function ($model): void {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model): void {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
