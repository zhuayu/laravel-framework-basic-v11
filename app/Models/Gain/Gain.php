<?php

namespace App\Models\Gain;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Gain extends Model
{
    use SoftDeletes;

    protected $table = 'gains';

    protected $fillable = [
        'name',
        'slug',
    ];
}
