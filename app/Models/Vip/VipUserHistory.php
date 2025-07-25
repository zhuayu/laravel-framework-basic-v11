<?php

namespace App\Models\Vip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class VipUserHistory extends Model
{
    use SoftDeletes;
    const TYPE_ADD = 1; // 增加
    const TYPE_SUB = -1; // 减少

    protected $table = 'vip_user_history';

    protected $fillable = [
        'user_id',
        'vip_id',
        'sku_id',
        'order_id',
        'number',
        'type',
        'remark',
        'start_at',
        'expired_at',
    ];

    public function sku(){
        return $this->belongsTo('App\Models\Vip\VipSku','sku_id','id');
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function scopeInName($query, $name)
    {
        return $query->whereHasIn('user', function($q) use ($name){
            $q->where('nickname','like', '%'.$name.'%');
        });
    }

    public function scopeSearch($query, $params){
        if(isset($params['name'])){
            $query->inName($params['name']);
        }

        if(isset($params['user_id'])){
            $query->where('user_id', $params['user_id']);
        }
    }
}
