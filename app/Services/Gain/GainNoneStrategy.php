<?php
namespace App\Services\Gain;
use App\Models\Gain\GainUserRule;

class GainNoneStrategy extends GainBase implements GainInterface {

    public function calculate(GainUserRule $gainUserRule) {

        $this->setGainHistory($gainUserRule);

    }

}
