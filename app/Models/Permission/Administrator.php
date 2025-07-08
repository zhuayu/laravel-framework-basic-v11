<?php

namespace App\Models\Permission;

use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Administrator extends Authenticatable implements LaratrustUser
{
    use HasRolesAndPermissions;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'phone',
        'visited_at',
    ];

    public function role() {
        return $this->hasMany(RoleUser::class, 'user_id', 'id');
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }
}
