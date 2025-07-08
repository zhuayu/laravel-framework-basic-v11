<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    protected $table = 'permission_groups';

    protected $fillable = [
        'tab_id',
        'name',
        'display_name',
        'sort',
    ];

    public $timestamps = false;

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'group_id', 'id')
            ->orderBy('sort')->orderBy('id');
    }

    public function groupPermissionLists()
    {
        return $this->with('permissions')
            ->orderBy('sort')
            ->get();
    }

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
