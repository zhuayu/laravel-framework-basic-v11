<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatAppUser extends Model
{

    protected $table = 'wechat_app_users';

    protected $fillable = [
        'app_id',
        'openid',
        'unionid',
        'nickname',
        'gender',
        'country',
        'province',
        'city',
        'avatar_url',
        'session_key',
        'subscribe',
    ];
}
