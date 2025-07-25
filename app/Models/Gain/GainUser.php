<?php

namespace App\Models\Gain;

use Illuminate\Database\Eloquent\Model;

class GainUser extends Model
{
    protected $table = 'gain_users';

    protected $fillable = [
        'user_id',
        'gain_id',
        'slug',
        'number',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'user_id', 'id', 'gain_id',
    ];


    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
