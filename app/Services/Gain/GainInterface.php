<?php
namespace App\Services\Gain;
use App\Models\Gain\GainUserRule;

interface GainInterface {
    public function calculate(GainUserRule $gainUserRule);
}



