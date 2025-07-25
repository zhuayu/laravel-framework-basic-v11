<?php

namespace App\Models\Gain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use DB;

class GainUserRule extends Model
{
    use SoftDeletes;

    protected $table = 'gain_user_rules';

    protected $fillable = [
        'user_id',
        'gain_id',
        'gain_rule_id',
        'rate',
        'slug',
        'rate_start_at',
        'rate_end_at',
    ];

    public function scopeRang($query, $rang)
    {
        $now = Carbon::now();
        return $rang === 'daily'
            ? $query->where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d'))"), $now->toDateString())
            : $query;
    }
}
