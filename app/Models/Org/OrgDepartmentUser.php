<?php

namespace App\Models\Org;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgDepartmentUser extends Model
{
    protected $table = 'org_dept_users';
    protected $fillable = ['org_id', 'dept_id', 'user_id', 'phone', 'name', 'status'];
    public $timestamps = false;

    public function department(): BelongsTo
    {
        return $this->belongsTo(OrgDepartment::class, 'dept_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function scopeSearch($query, $params) {
        if(isset($params['dept_id'])) {
            $query->where('dept_id', $params['dept_id']);
        }

        if(isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }


        if(isset($params['phone'])) {
            $query->where('phone', 'like', '%'.$params['phone'].'%');
        }

        if(isset($params['name'])) {
            $query->where('name', 'like', '%'.$params['name'].'%');

        }
    }
}