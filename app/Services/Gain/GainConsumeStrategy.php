<?php
namespace App\Services\Gain;

use App\Models\Gain\GainUserRule;

class GainConsumeStrategy extends GainBase implements GainConsumeInterface {

    public function consume($gainUserRule)
    {
        $this->setGainHistory($gainUserRule, true);
    }
}
