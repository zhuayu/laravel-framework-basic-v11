<?php
namespace App\Services\Gain;
use App\Models\Gain\GainUserRule;

class GainOnceStrategy extends GainBase implements GainInterface {

    public function calculate(GainUserRule $gainUserRule) {
        $gainRule = $this->gainRule;
        // 判断频次是否超过关联规则规定(-1 代表无限）
        if ($gainRule->rate != -1
            && $gainUserRule->rate >= $gainRule->rate
        ) {
            throw new \Exception('超过规定次数');
        }

        $this->setGainHistory($gainUserRule);
    }
}