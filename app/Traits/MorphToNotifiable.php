<?php

namespace App\Traits;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait MorphToNotifiable
{
    /**
     * Get all notifications for this notifiable model.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Get unread notifications for this notifiable model.
     */
    public function unreadNotifications(): MorphMany
    {
        return $this->notifications()->unread();
    }

    /**
     * Get read notifications for this notifiable model.
     */
    public function readNotifications(): MorphMany
    {
        return $this->notifications()->read();
    }
}

