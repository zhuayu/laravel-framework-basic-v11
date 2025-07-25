<?php

namespace App\Models\Vip;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class VipSku extends Model
{
    use SoftDeletes;

    protected $table = 'vip_skus';

    protected $fillable = [
        'slug',
        'name',
        'current_price',
        'origin_price',
        'cover_url',
        'number',
        'sold_count',
        'is_online',
        'stock',
        'vip_id'
    ];

    // VIP 对应 ID
    const ONE_DAY_VIP = 1;
    const ONE_MONTH_VIP = 2;
    const ONE_YEAR_VIP = 3;

    const ONE_DAY_VVIP = 4;
    const ONE_MONTH_VVIP = 5;
    const ONE_YEAR_VVIP = 6;

    const ONE_DAY_SVIP = 7;
    const ONE_MONTH_SVIP = 8;
    const ONE_YEAR_SVIP = 9;

}
