<?php

namespace App\Models\Org;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrgGroup extends Model
{
    protected $table = 'org_groups';
    protected $fillable = [
        'org_id', 
        'name', 
        'type', 
        'creator_id',
        'member_count'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'creator_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrgGroupUser::class, 'group_id');
    }

    public function scopeSearch($query, $params) {
        if(isset($params['creator_id'])) {
            $query->where('creator_id', $params['creator_id']);
        }

        if(isset($params['name'])) {
            $query->where('name', 'like', '%'.$params['name'].'%');
        }

        if(isset($params['user_id'])) {
            $query->whereHasIn('members', function($q) use ($params){
                $q->where('user_id', $params['user_id']);
            });
        }
    }
}