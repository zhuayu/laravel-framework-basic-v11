<?php

namespace App\Models\Gain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GainUserHistory extends Model
{
    use SoftDeletes;

    protected $table = 'gain_user_histories';

    protected $fillable = [
        'user_id',
        'gain_id',
        'gain_rule_id',
        'pay_id',
        'slug',
        'name',
        'action',
        'param',
        'param_value',
        'number',
        'previous_total',
        'current_total',
        'type',
        'remark',
        'creator_id',
    ];

    const TYPE_INCOME = 1;
    const TYPE_EXPEND = 2;
    const TYPE_CANCEL = 3;
    const TYPES = [
        self::TYPE_INCOME => '收入',
        self::TYPE_EXPEND => '支出',
        self::TYPE_CANCEL => '退还'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }


    public function scopeSearch($query, $params)
    {
        if (isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }

        if (isset($params['type'])) {
            $query->where('type', $params['type']);
        }
    }
}
