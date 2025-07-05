<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatApp extends Model
{
    protected $table = 'wechat_apps';

    protected $fillable = [
        'type',
        'name',
        'original_id',
        'app_id',
        'secret',
        'token',
        'aes_key',
        'payment_merchant_id',
        'payment_key',
        'payment_cert_path',
        'payment_key_path',
    ];
}
