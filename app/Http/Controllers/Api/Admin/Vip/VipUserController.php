<?php

namespace App\Http\Controllers\Api\Admin\Vip;

use App\Http\Controllers\Controller;
use App\Models\Vip\VipUser;
use App\Services\Vip\VipService;
use App\Http\Requests\Api\Admin\Vip\VipUserIndexRequest;
use App\Http\Requests\Api\Admin\Vip\VipUsersStoreRequest;
use App\Http\Resources\PaginationCollection;

class VipUserController extends Controller
{
    public function index(VipUserIndexRequest $request){
        $data = $request->validated();
        $vipUsers = VipUser::with(['vip', 'user'])
            ->search($data)
            ->orderBy('id', 'desc')
            ->paginate($request->input('page_size', 10));
        return new PaginationCollection($vipUsers);
    }

    public function store(VipUsersStoreRequest $request){
        $data = $request->validated();
        $api = new VipService();
        $users = $data['users'];
        $type = $data['type'];
        $remark = isset($data['remark']) ? $data['remark'] : null;
        foreach ($users as $user) {
            if($type > 0){
                $api->addVip($user, $data['vip_sku_id'], $data['number'], $remark, null);
            }else{
                $api->deleteVip($user, $data['vip_sku_id'], $data['number'], $remark, null);
            }
        }
        return $this->success(null);
    }
}
