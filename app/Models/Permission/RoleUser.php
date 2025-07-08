<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $table = 'role_user';

    protected $fillable = [
        'user_id',
        'role_id',
        'user_type',
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;

}
