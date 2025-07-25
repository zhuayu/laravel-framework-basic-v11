<?php

namespace App\Services\Vip;

use App\Models\User;
use App\Models\Vip\VipSku;
use App\Services\Vip\VipService;

class VipGiveService
{
    // 用户提交认证资料，赠送 3 天 VVIP 高级版
    public function authenticationSubmit(User $user)
    {
        $api = new VipService();
        $api->addVip($user->id, VipSku::ONE_DAY_VVIP, 3, "提交认证资料赠送高级版会员3天");
    }
}
