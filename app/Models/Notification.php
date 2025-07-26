<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $casts = [
        'data' => 'json',
    ];

    protected $fillable = [
        'type',
        'data',
    ];
}
