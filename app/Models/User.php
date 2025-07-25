<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable {
        notify as protected laravelNotify;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'unionid',
        'phone_prefix',
        'phone',
        'name',
        'account',
        'password',
        'avatar_url',
        'vip_slug',
        'vip_expired',
        'notification_count',
        'config',
        'extra',
        'visited_at',
        'disabled',
    ];

    protected $casts = [
        'config' => 'json',
        'extra' => 'json',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
