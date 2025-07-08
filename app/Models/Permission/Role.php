<?php

namespace App\Models\Permission;

use Cache;
use Laratrust\Models\Role as RoleModel;
use App\Models\Permission\Administrator;


class Role extends RoleModel
{

    protected $perPage = 50;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function administrators()
    {
        return $this->belongsToMany(Administrator::class, 'role_user', 'role_id', 'user_id');
    }

    protected $hidden = [
        'created_at', 'updated_at'
    ];

}
