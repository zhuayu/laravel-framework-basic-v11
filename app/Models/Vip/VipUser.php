<?php

namespace App\Models\Vip;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class VipUser extends Model
{
    use SoftDeletes;
    protected $table = 'vip_users';

    protected $fillable = [
        'user_id',
        'vip_id',
        'slug',
        'expired_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'user_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vip()
    {
        return $this->belongsTo(Vip::class, 'vip_id');
    }

    public function scopeSearch($query, $params)
    {
        if (isset($params['nickname'])) {
            $nickname = $params['nickname'];
            $query->whereHasIn('user', function ($q) use ($nickname) {
                return $q->where('nickname', 'like', '%' . $nickname . '%');
            });
        }
        if (isset($params['phone'])) {
            $phone = $params['phone'];
            $query->whereHasIn('user', function ($q) use ($phone) {
                return $q->where('phone', 'like', '%' . $phone . '%');
            });
        }

        if (isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }

        if (isset($params['vip_id'])) {
            $query->where('vip_id', $params['vip_id']);
        }

        if (isset($params['is_valid']) && $params['is_valid'] == 1) {
            $query->whereDate('expired_at', '>=', Carbon::now());
        }
    }
}
