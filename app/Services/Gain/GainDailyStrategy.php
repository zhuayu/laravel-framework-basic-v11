<?php
namespace App\Services\Gain;

use App\Models\Gain\GainUserHistory;
use App\Models\Gain\GainUserRule;
use Carbon\Carbon;
use DB;

class GainDailyStrategy extends GainBase implements GainInterface {

    public function calculate(GainUserRule $gainUserRule) {
        // 判断频次是否超过关联规则规定(-1 代表无限）
        $gainRule = $this->gainRule;
        $now = Carbon::now();
        if ($gainRule->rate != -1
            && $gainUserRule->rate >= $gainRule->rate
        ) {
            throw new \Exception('超过规定次数');
        }

        // 通过规则计算用户本次可增加的极客币
        $paramValue = $this->getRuleParams($gainRule, $this->params);
        $number = $this->getRuleParseResult($gainRule->rule, $paramValue);

        // 如果规则频次是daily，获取当前历史记录，判断总获取总量是否超过计算规则中 max_total
        if ($gainRule->max_total != -1) {
            $histories = GainUserHistory::where([
                    'user_id' => $this->user->id,
                    'gain_rule_id' => $gainRule->id,
                ])
                ->where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d'))"), $now->toDateString())
                ->get();

            $todayGotTotalNumber = $histories->sum('number') + $number;
            if ($todayGotTotalNumber > $gainRule->max_total) {
                throw new \Exception('加上当前值，获将超过最大设定值：' . $gainRule->max_total);
            }
        }

        $this->setGainHistory($gainUserRule);
    }
}
