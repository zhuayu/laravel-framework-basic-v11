<?php

namespace App\Models\Org;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use SoftDeletes;

    protected $table = 'organizations';
    protected $fillable = [
        'name',
        'cover_url',
        'status', 
        'creator_id',
        'member_count'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_REJECTED = 'rejected';

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'creator_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(OrgDepartment::class, 'org_id');
    }

    public function deptUsers(): HasMany
    {
        return $this->hasMany(OrgDepartmentUser::class, 'org_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(OrgGroup::class, 'org_id');
    }

    public function groupUsers(): HasMany
    {
        return $this->hasMany(OrgGroupUser::class, 'org_id');
    }

    public function scopeSearch($query, $params) {
        if(isset($params['creator_id'])) {
            $query->where('creator_id', $params['creator_id']);
        }

        if(isset($params['name'])) {
            $query->where('name', 'like', '%'.$params['name'].'%');

        }

        if(isset($params['user_id'])) {
            $query->whereHasIn('deptUsers', function($q) use ($params){
                $q->where('user_id', $params['user_id']);
            });
        }
    }
}