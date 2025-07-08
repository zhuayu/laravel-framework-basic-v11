<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Model;

class PermissionUser extends Model
{
    public $timestamps = false;

    protected $table = 'permission_user';

    protected $fillable = [
        'permission_id',
        'user_id',
        'user_type',
    ];

}
