<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Guard name used by spatie/laravel-permission for this model.
     * This allows API auth via Sanctum while keeping default auth guard as 'web'.
     *
     * @var string
     */
    protected $guard_name = 'sanctum';

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')->withTimestamps();
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->using(ProjectMember::class)
            ->withPivot(['role', 'joined_at', 'preferences'])
            ->withTimestamps();
    }

    public function projectMemberships()
    {
        return $this->hasMany(ProjectMember::class, 'user_id');
    }

    /**
     * Get all notifications for this user.
     */
    public function userNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Get unread notifications for this user.
     */
    public function unreadUserNotifications()
    {
        return $this->userNotifications()->unread();
    }

    /**
     * Get read notifications for this user.
     */
    public function readUserNotifications()
    {
        return $this->userNotifications()->read();
    }
}
