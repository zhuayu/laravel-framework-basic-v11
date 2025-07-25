<?php

namespace App\Services\Gain;

use App\Models\Gain\GainRule;
use App\Models\Gain\GainUserRule;
use App\Models\Gain\GainUserHistory;
use Carbon\Carbon;

class GainCalculator {
    protected $user = null;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /*
     * 产生
     */
    public function productCalculate($params)
    {
        
        try {
            // 获取计算规则
            $gainRule = $this->getGainRule($params);
            $gainUserRule = $this->getGainUserRule($gainRule);
            // 设置计算类型
            $user = $this->user;
            $params = array_merge($params,
                [
                    'uid' => $user->id,
                    'type' => GainUserHistory::TYPE_INCOME
                ]);
            
            // 根据规则计算
            switch ($gainRule->rang) {
                case 'daily' :
                    (new GainDailyStrategy($user,$gainRule,$params))->calculate($gainUserRule);
                    break;
                case 'once' :
                    (new GainOnceStrategy($user,$gainRule,$params))->calculate($gainUserRule);
                    break;
                case 'none' :
                    (new GainNoneStrategy($user,$gainRule,$params))->calculate($gainUserRule);
                    break;
            }

            return null;
        } catch (\Exception $e) {
            $logger = fileLogger('gain-logs', 'error');
            $logger->info(json_encode(array_merge($params, ['error_message' => $e->getMessage()]), JSON_UNESCAPED_UNICODE));
            return $e->getMessage();
        }
    }

    /*
     * 消费极客币
     */
    public function consumeCalculate($params)
    {
        try{
            // 获取计算规则
            $gainRule = $this->getGainRule($params);
            $gainUserRule = $this->getGainUserRule($gainRule);
            $params = array_merge($params,
                [
                    'uid' => $this->user->id,
                    'type' => GainUserHistory::TYPE_EXPEND
                ]);
            (new GainConsumeStrategy($this->user,$gainRule,$params))->consume($gainUserRule);
            return true;
        } catch (\Exception $e) {
            $logger = fileLogger('gain-logs', 'error');
            $logger->info(json_encode(array_merge($params, ['error_message' => $e->getMessage()]), JSON_UNESCAPED_UNICODE));
            return false;
        }
    }

    /*
     * 退还
     */
    public function cancelCalculate($params)
    {
        try{
            // 获取计算规则
            $gainRule = $this->getGainRule($params);
            $gainUserRule = $this->getGainUserRule($gainRule);
            $params = array_merge($params,
                [
                    'uid' => $this->user->id,
                    'type' => GainUserHistory::TYPE_CANCEL
                ]);
            (new GainNoneStrategy($this->user, $gainRule, $params))->calculate($gainUserRule);
            return true;
        } catch (\Exception $e) {
            $logger = fileLogger('gain-logs', 'error');
            $logger->info(json_encode(array_merge($params, ['error_message' => $e->getMessage()]), JSON_UNESCAPED_UNICODE));
            return false;
        }
    }

    protected function getGainRule($params)
    {
        $gainRule = GainRule::where([
            'action' => $params['action'],
        ])->first();

        if (!$gainRule) {
            throw new \Exception('积分/货币规则不存在');
        }

        return $gainRule;
    }

    protected function getGainUserRule($gainRule) {
        $now = Carbon::now();
        $rang = $gainRule->rang;
        // 获取用户关联规则，没有就建立
        $userRule = GainUserRule::where([
            'gain_rule_id' => $gainRule->id,
            'user_id' => $this->user->id,
        ])->rang($rang)->first();

        if (!$userRule) {
            $rateStartAt = ($rang === 'daily')
                ? $now->startOfDay()->toDateTimeString()
                : null;
            $rateEndAt = ($rang === 'daily')
                ? $now->endOfDay()->toDateTimeString()
                : null;
            $userRule = GainUserRule::create([
                'user_id' => $this->user->id,
                'gain_id' => $gainRule->gain_id,
                'slug' => $gainRule->slug,
                'gain_rule_id' => $gainRule->id,
                'rate' => 0,
                'rate_start_at' => $rateStartAt,
                'rate_end_at' => $rateEndAt
            ]);
        }

        return $userRule;
    }
}
