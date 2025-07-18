<?php

namespace App\Models\Org;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgGroupUser extends Model
{
    protected $table = 'org_group_users';
    protected $fillable = ['org_id', 'group_id', 'user_id', 'creator_id', 'role'];
    protected $casts = ['role' => 'string'];
    public $timestamps = false;

    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    const ROLE_MEMBER = 'member';

    public function group(): BelongsTo
    {
        return $this->belongsTo(OrgGroup::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}