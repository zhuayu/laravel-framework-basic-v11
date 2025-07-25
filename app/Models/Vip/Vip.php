<?php

namespace App\Models\Vip;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Vip extends Model
{
    use SoftDeletes;

    protected $table = 'vip';

    protected $fillable = [
        'slug',
        'name',
    ];

}
