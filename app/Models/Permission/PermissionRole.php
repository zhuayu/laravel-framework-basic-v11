<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    protected $table = 'permission_role';

    protected $fillable = [
        'permission_id',
        'role_id',
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;

}
