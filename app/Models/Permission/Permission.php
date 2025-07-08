<?php

namespace App\Models\Permission;

use Laratrust\Models\Permission as PermissionModel;

class Permission extends PermissionModel
{
    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'sort',
        'group_id',
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
