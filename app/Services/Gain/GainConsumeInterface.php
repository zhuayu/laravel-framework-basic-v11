<?php
namespace App\Services\Gain;
use App\Models\Gain\GainUserRule;

interface GainConsumeInterface {
    public function consume(GainUserRule $gainUserRule);
}
