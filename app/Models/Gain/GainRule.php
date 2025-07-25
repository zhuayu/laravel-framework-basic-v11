<?php

namespace App\Models\Gain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GainRule extends Model
{
    use SoftDeletes;

    protected $table = 'gain_rules';

    protected $fillable = [
        'slug',
        'name',
        'gain_id',
        'action',
        'param',
        'rang',
        'rate',
        'rule',
        'max_total',
        'status',
        'sort',
    ];

    public function history(){
        return $this->hasMany(GainUserHistory::class, 'gain_rule_id', 'id');
    }
}
