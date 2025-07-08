<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Model;

class PermissionTab extends Model
{
    protected $table = 'permission_tabs';

    protected $fillable = [
        'name',
        'display_name',
        'sort',
    ];

    public function permissionGroups()
    {
        return $this->hasMany(PermissionGroup::class, 'tab_id', 'id')
            ->orderBy('sort')->orderBy('id');
    }

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
