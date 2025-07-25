<?php

namespace App\Services\Vip;

use App\Models\User;
use App\Models\Vip\Vip;
use App\Models\Vip\VipSku;
use App\Models\Vip\VipUser;
use App\Models\Vip\VipUserHistory;
use Carbon\Carbon;
use DB;

class VipService
{
    // 升级会员类型
    const VIP_UPGRADE_TYPE_OPEN = 1;  // 开通会员（无会员开通）
    const VIP_UPGRADE_TYPE_UPGRADE = 2; // 升级会员（有会员升级）
    const VIP_UPGRADE_TYPE_DEGRADE = 3; // 延长会员（有会员降级）
    const VIP_UPGRADE_TYPE_EXTEND = 4;   // 延长会员（同会员续费）

    
    public function addVip($userId, $vipSkuId, $number = 1, $remark = null, $orderId = null, $startDate = null)
    {
        $user = User::findOrFail($userId);
        $sku = VipSku::findOrFail($vipSkuId);
        $targetVip = Vip::findOrFail($sku->vip_id);
        $amount = $sku->number * $number;
        //***重构开始***
        $hasOrder = $orderId ? true : false;
        $upgradeDatas = $this->getUpgradeVipDatas($userId, $vipSkuId, $amount, $hasOrder);
        $upgradeType = $upgradeDatas['upgradeType'];
        $upgradeVipId = $upgradeDatas['upgradeVipId'];
        $upgradeVipSkuId = $upgradeDatas['upgradeVipSkuId'];
        $upgradeVipDays = $upgradeDatas['upgradeVipDays'];
        // ***重构结束***

        $vip = Vip::findOrFail($upgradeVipId);
        $userVip = VipUser::where([
            'user_id' => $userId,
            'vip_id' => $upgradeVipId
        ])->first();


        $carbonNow = $startDate ? Carbon::create($startDate) : Carbon::now();

        $startAt = ($userVip && Carbon::create($userVip->expired_at) > $carbonNow)
            ? Carbon::create($userVip->expired_at)
            : ($startDate ? Carbon::create($startDate) : Carbon::now());
        $expiredAt = ($userVip && Carbon::create($userVip->expired_at) > $carbonNow)
            ? Carbon::create($userVip->expired_at)->addDays($upgradeVipDays)
            : ($startDate ? Carbon::create($startDate) : Carbon::now())->addDays($upgradeVipDays);
        $addRemark = $remark . '自动转换:' . '升级类型:'  . $upgradeType . ',' . '目标会员:' . $targetVip->slug . ',' . "目标天数:" . $amount . ',';

        DB::beginTransaction();
        try {
            VipUser::updateOrCreate([
                'user_id' => $userId,
                'vip_id' => $upgradeVipId,
                'slug' => $vip->slug
            ], [
                'expired_at' => $expiredAt,
            ]);
            VipUserHistory::create([
                'vip_id' => $upgradeVipId,
                'user_id' => $userId,
                'sku_id' => $upgradeVipSkuId,
                'order_id' => $orderId,
                'number' => $upgradeVipDays,
                'remark' => $addRemark,
                'start_at' => $startAt,
                'expired_at' => $expiredAt,
                'type' => VipUserHistory::TYPE_ADD
            ]);

            // 除upgradeVip，其他有效期内的会员需立即到期
            $deleteVipUsers = VipUser::whereNotIn('vip_id', [$upgradeVipId])
                ->where(['user_id' => $userId])
                ->where('expired_at', '>', Carbon::now())
                ->get();
            if (count($deleteVipUsers) > 0) {
                foreach ($deleteVipUsers as $deleteVipUser) {

                    $date_vip_expired = Carbon::parse($deleteVipUser->expired_at);
                    $date_now = Carbon::now();
                    $diffNumber = ceil($date_now->diffInHours($date_vip_expired, false) / 24);

                    $deleteVipSkuId = $this->getOneDayVipSku($deleteVipUser->vip_id);
                    $deleteRemark = $remark . '自动转换:' . '升级类型:'  . $upgradeType . ',' . '升级会员:' . $vip->slug . ',' . "升级天数:" . $upgradeVipDays;
                    $deleteVipUser->update([
                        'expired_at' =>  $date_now
                    ]);
                    VipUserHistory::create([
                        'vip_id' => $vip->id,
                        'user_id' => $userId,
                        'sku_id' => $deleteVipSkuId,
                        'order_id' => $orderId,
                        'number' => $diffNumber,
                        'remark' =>  $deleteRemark,
                        'start_at' =>  $date_now,
                        'expired_at' => $date_now,
                        'type' => VipUserHistory::TYPE_SUB
                    ]);
                }
            }

            // 更新用户数据
            $user->update([
                'vip_slug' => $vip->slug,
                'vip_expired' => $expiredAt,
            ]);

            DB::commit();
            return [
                'code' => 0,
                'data' => [
                    'expired_at' => $expiredAt
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("添加VIP失败:" . $e->getMessage());
        }
    }

    public function deleteVip($userId, $vipSkuId, $number = 1, $remark = null, $orderId = null)
    {
        $user = User::findOrFail($userId);
        $sku = VipSku::findOrFail($vipSkuId);
        $vip = Vip::findOrFail($sku->vip_id);
        $now = Carbon::now();
        $amount = $sku->number * $number;
        $userVip = VipUser::where([
            'user_id' => $userId,
            'vip_id' => $vip->id,
        ])->first();

        $expiredAt = $userVip
            ? Carbon::create($userVip->expired_at)->subDays($amount)
            : $now;
        $expiredAt = $expiredAt < $now ? $now : $expiredAt;
        DB::beginTransaction();
        try {
            VipUser::updateOrCreate([
                'user_id' => $userId,
                'vip_id' => $vip->id,
                'slug' => $vip->slug,
            ], [
                'expired_at' => $expiredAt,
            ]);

            VipUserHistory::create([
                'vip_id' => $vip->id,
                'user_id' => $userId,
                'sku_id' => $vipSkuId,
                'order_id' => $orderId,
                'number' => $amount,
                'remark' => $remark,
                'start_at' => Carbon::now(),
                'expired_at' => $expiredAt,
                'type' => VipUserHistory::TYPE_SUB
            ]);

            // 更新用户数据
            $user->update([
                'vip_slug' => $vip->slug,
                'vip_expired' => $expiredAt,
            ]);

            DB::commit();
            return [
                'code' => 0,
                'data' => [
                    'expired_at' => $expiredAt
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("撤回VIP失败:" . $e->getMessage());
            return [
                'code' => 1,
                'msg' => "撤回VIP失败:" . $e->getMessage()
            ];
        }
    }

    public function getHeightVip($userId)
    {
        // 1. 获取用户拥有的所有未过期vip
        $dateNow = Carbon::now();
        $userVips = VipUser::where(['user_id' => $userId])
            ->where('expired_at', '>', $dateNow)
            ->get()
            ->toArray();;
        $vip = null;
        $vvip = null;
        $svip = null;

        // 2. 返回该用户最高等级vip
        foreach ($userVips as $userVip) {
            // $userVip['expired_days'] = Carbon::parse($userVip['expired_at'])->diffInDays($dateNow);
            $userVip['expired_days'] = ceil($dateNow->diffInHours(Carbon::parse($userVip['expired_at']), false) / 24);
            switch ($userVip['slug']) {
                case 'VIP':
                    $vip = $userVip;
                    $vip['status'] = 2;
                    break;
                case 'VVIP':
                    $vvip = $userVip;
                    $vvip['status'] = 3;
                    break;
                case 'SVIP':
                    $svip = $userVip;
                    $svip['status'] = 4;
                    break;
            }
        }
        return $svip ? $svip : ($vvip ? $vvip : $vip);
    }

    // 获取会员状态，包含过期的会员
    public function getHeightContainExpiredVip($userId)
    {
        $dateNow = Carbon::now();

        $vip = null;
        $vvip = null;
        $svip = null;

        // 1. 获取用户拥有的所有vip
        $userVips = VipUser::where([
            'user_id' => $userId,
        ])
            ->get()
            ->toArray();

        // 2. 返回该用户最近未过期的最高等级vip
        // 2.1 获取未过期的vip
        $unexpiredVips = array_filter($userVips, function ($userVip) use ($dateNow) {
            return $userVip['expired_at'] > $dateNow;
        });
        // 2.2
        foreach ($unexpiredVips as $userVip) {
            $userVip['expired_days'] = Carbon::parse($userVip['expired_at'])->diffInDays($dateNow);
            switch ($userVip['slug']) {
                case 'VIP':
                    $vip = $userVip;
                    $vip['status'] = 2;
                    break;
                case 'VVIP':
                    $vvip = $userVip;
                    $vvip['status'] = 3;
                    break;
                case 'SVIP':
                    $svip = $userVip;
                    $svip['status'] = 4;
                    break;
            }
        }
        $res = $svip ? $svip : ($vvip ? $vvip : $vip);

        if (!$res) {
            // 3. 返回该用户最近过期的最高等级vip
            // 3.1 获取已过期的vip
            $expiredVips = array_filter($userVips, function ($userVip) use ($dateNow) {
                return $userVip['expired_at'] <= $dateNow;
            });
            // 3.2 排序已过期的vip
            uasort($expiredVips, function ($a, $b) {
                return $a['expired_at'] < $b['expired_at'];
            });

            // 3.3
            $expiredAt = null;
            foreach ($expiredVips as $userVip) {
                if (!$expiredAt || Carbon::parse($userVip['expired_at']) >= $expiredAt) {
                    $expiredAt = Carbon::parse($userVip['expired_at']);
                    $userVip['expired_days'] = $expiredAt->diffInDays($dateNow);
                    switch ($userVip['slug']) {
                        case 'VIP':
                            $vip = $userVip;
                            $vip['status'] = 0;
                            break;
                        case 'VVIP':
                            $vvip = $userVip;
                            $vvip['status'] = 0;
                            break;
                        case 'SVIP':
                            $svip = $userVip;
                            $svip['status'] = 0;
                            break;
                    }
                } else {
                    break;
                }
            }
            $res = $svip ? $svip : ($vvip ? $vvip : $vip);
        }
        return $res;
    }

    /**
     * 计算 Vip 升级类型的价格和天数
     *
     * @param int $userId 用户ID
     * @param int $targetVipSkuId 目标会员SKU ID
     * @param int $targetVipDays 目标会员天数
     * @param int $hasOrder 是否主动购买
     * @return array 包含 vip 升级类型，换算会员，升级会员，升级会员skuId，换算会员价格，升级会员天数的数组
     */
    public function getUpgradeVipDatas($userId, $targetVipSkuId, $targetVipDays = 0, $hasOrder = false)
    {
        $user = User::findOrFail($userId);
        $targetVipSku = VipSku::findOrFail($targetVipSkuId);
        // 获取用户当前最高级别会员
        $currentVipUser = $this->getHeightVip($userId);

        $currentVipDays = $currentVipUser ? $currentVipUser['expired_days'] : 0;
        $currentVipId = $currentVipUser ? $currentVipUser['vip_id'] : null;
        $calculateUpgradeDatas = $this->getCalculateUpgradeVipDatas($targetVipSku, $currentVipId, $currentVipDays, $targetVipDays);

        $calculateVipId = $calculateUpgradeDatas['calculateVipId'];
        $calculateVipDays = $calculateUpgradeDatas['calculateVipDays'];
        $upgradeVipId = $calculateUpgradeDatas['upgradeVipId'];
        $upgradeVipSkuId = $calculateUpgradeDatas['upgradeVipSkuId'];
        $upgradeVipDays = $calculateUpgradeDatas['upgradeVipDays'];
        $vipUpgrateType = $calculateUpgradeDatas['vipUpgrateType'];

        // 用户要付的价格，主动购买为目标会员价格，后台赠送为 0
        $upgradeVipSkuPrice = $hasOrder ? (int)$targetVipSku->current_price : 0;

        // 情况1: 如果用户没有会员或会员已过期，直接返回原价
        if ($vipUpgrateType == self::VIP_UPGRADE_TYPE_OPEN) {
            return [
                'upgradeType' => $vipUpgrateType,
                'upgradePrice' => $upgradeVipSkuPrice,
                'upgradeVipId' => $upgradeVipId,
                'upgradeVipSkuId' => $upgradeVipSkuId,
                'upgradeVipDays' =>  $upgradeVipDays,
            ];
        }

        // 情况2: 如果是同级别会员，则为续费
        if ($vipUpgrateType == self::VIP_UPGRADE_TYPE_EXTEND) {
            return [
                'upgradeType' => $vipUpgrateType,
                'upgradePrice' => $upgradeVipSkuPrice,
                'upgradeVipId' => $upgradeVipId,
                'upgradeVipSkuId' => $upgradeVipSkuId,
                'upgradeVipDays' => $upgradeVipDays
            ];
        }


        // 如果目标会员级别高于or低于当前会员级别，为升级or降级，都是延长会员
        // 换算总价格
        $calculateVipPrice = $this->getCalculateVipPrice($calculateVipId, $calculateVipDays);

        // 升级会员年费日价格
        $upgradeYearVipSkuDailyPrice  = $this->getYearVipSkuPrice($upgradeVipId) / 365;

        // 计算价差
        // 主动购买使用升级会员的sku价格，主动购买不能降级，后台赠送升级、降级sku价格为0
        $priceDiff = floor($upgradeVipSkuPrice -  $calculateVipPrice);
        $priceDiff = max(0, $priceDiff); // 如果差价为负，则设为0

        // 换算升级天数
        $extraDays = ceil(($calculateVipPrice - $upgradeVipSkuPrice) / $upgradeYearVipSkuDailyPrice);
        $extraDays = max(0, $extraDays); // 如果换算升级天数为负，则设为0

        // 情况3: 如果目标会员级别低于当前会员级别，为降级
        if ($vipUpgrateType == self::VIP_UPGRADE_TYPE_DEGRADE) {
            $upgradeVipDays = $extraDays;
            return [
                'upgradeType' => $vipUpgrateType,
                'upgradePrice' => $priceDiff,
                'upgradeVipId' => $upgradeVipId,
                'upgradeVipSkuId' => $upgradeVipSkuId,
                'upgradeVipDays' => $upgradeVipDays
            ];
        }

        // 情况4: 如果目标会员级别高于当前会员级别，为升级
        //如果换算价值大于升级会员价格，升级天数是换算升级天数 + 升级会员天数，否则为升级会员天数
        $upgradeVipDays += $extraDays;

        return [
            'upgradeType' => $vipUpgrateType,
            'upgradePrice' => $priceDiff,
            'upgradeVipId' => $upgradeVipId,
            'upgradeVipSkuId' => $upgradeVipSkuId,
            'upgradeVipDays' => $upgradeVipDays
        ];
    }


    /**
     * 计算-会员年费价格
     *
     * @param int $vipId 会员id
     * @return int 会员年费
     */
    public function getYearVipSkuPrice($vipId)
    {
        if (!$vipId) return 0;
        $vip = Vip::find($vipId);
        if (!$vip) return 0;

        $yearVipSkuIdMap = [
            'VIP' => VipSku::ONE_YEAR_VIP,
            'VVIP' => VipSku::ONE_YEAR_VVIP,
            'SVIP' => VipSku::ONE_YEAR_SVIP
        ];
        $yearVipSkuId = $yearVipSkuIdMap[$vip->slug];
        $yearVipSku = VipSku::find($yearVipSkuId);
        $yearVipSkuPrice = $yearVipSku->current_price;
        return $yearVipSkuPrice;
    }

    /**
     * 获取各会员天skuId
     *
     * @param int $vipId 会员id
     * @return int oneDayVipSkuId
     */
    public function getOneDayVipSku($vipId)
    {
        if (!$vipId) return null;
        $vip = Vip::find($vipId);
        if (!$vip) return null;

        $oneDayVipSkuIdMap = [
            'VIP' => VipSku::ONE_DAY_VIP,
            'VVIP' => VipSku::ONE_DAY_VVIP,
            'SVIP' => VipSku::ONE_DAY_SVIP
        ];
        $oneDayVipSkuId = $oneDayVipSkuIdMap[$vip->slug];
        return $oneDayVipSkuId;
    }

    /**
     * 计算-换算vip天数的换算价格
     *
     * @param model $calculateVipId 换算会员Id
     * @param int $calculateVipDays 换算会员的天数
     * @return int 换算vip天数的换算价格
     */
    public function getCalculateVipPrice($calculateVipId, $calculateVipDays)
    {
        // 年费价格
        $yearVipSkuPrice = $this->getYearVipSkuPrice($calculateVipId);
        // 换算vip天数的价格
        $yearVipSkuDailyPrice = $yearVipSkuPrice / 365; //年费日价格
        $price = $yearVipSkuDailyPrice * $calculateVipDays;
        return $price;
    }

    /**
     * 计算-换算会员、升级会员和天数
     *
     * @param model $currentVipUser 当前会员
     * @param model $targetVipSku 目标会员SKU
     * @param int $targetVipDays 目标会员天数
     * @param int $currentVipDays 当前用户会员天数
     * @return array
     * calculateVipId 换算会员Id, upgradeVipId升级会员Id,
     * calculateVipSkuId 换算会员skuId, upgradeVipSkuId升级会员skuId,
     * calculateDays 换算会员天数 upgradeVipDays升级会员天数 vipUpgrateType 升级会员类型
     */
    public function getCalculateUpgradeVipDatas(VipSku $targetVipSku, $currentVipId = null, $currentVipDays = 0, $targetVipDays = 0)
    {
        if (!$currentVipId) {
            return [
                'calculateVipId' => null,
                'upgradeVipId' => $targetVipSku->vip_id,
                'calculateVipSkuId' => null,
                'upgradeVipSkuId' => $targetVipSku->id,
                'upgradeVipDays' => $targetVipDays,
                'calculateVipDays' => 0,
                'vipUpgrateType' => self::VIP_UPGRADE_TYPE_OPEN
            ];
        }
        $currentVipSkuId = $this->getOneDayVipSku($currentVipId);

        $targetVipId =  $targetVipSku->vip_id;
        $upgradeFlag = $targetVipId >= $currentVipId ? true : false; //true为升级平级，flase为降级
        //换算会员 - 取低版本会员
        $calculateVipId =   $upgradeFlag ? $currentVipId : $targetVipId;
        $calculateVipSkuId = $upgradeFlag ? $currentVipSkuId : $targetVipSku->id;
        $calculateVipDays = $upgradeFlag ? $currentVipDays : $targetVipDays;
        //升级会员 - 取高版本会员
        $upgradeVipId =   $upgradeFlag ? $targetVipId : $currentVipId;
        $upgradeVipDays = $upgradeFlag ? $targetVipDays : $currentVipDays;
        $upgradeVipSkuId = $upgradeFlag ? $targetVipSku->id : $currentVipSkuId;

        $vipUpgrateType = null;
        if ($targetVipId == $currentVipId) {
            $vipUpgrateType = self::VIP_UPGRADE_TYPE_EXTEND;
        }
        if ($targetVipId > $currentVipId) {
            $vipUpgrateType = self::VIP_UPGRADE_TYPE_UPGRADE;
        }
        if ($targetVipId < $currentVipId) {
            $vipUpgrateType = self::VIP_UPGRADE_TYPE_DEGRADE;
        }
        return [
            'calculateVipId' => $calculateVipId,
            'upgradeVipId' => $upgradeVipId,
            'calculateVipSkuId' => $calculateVipSkuId,
            'upgradeVipSkuId' => $upgradeVipSkuId,
            'calculateVipDays' => $calculateVipDays,
            'upgradeVipDays' => $upgradeVipDays,
            'vipUpgrateType' => $vipUpgrateType,

        ];
    }
}
