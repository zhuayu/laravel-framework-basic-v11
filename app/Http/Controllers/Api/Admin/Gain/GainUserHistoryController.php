<?php

namespace App\Http\Controllers\Api\Admin\Gain;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Gain\GainUser;
use App\Models\Gain\GainUserHistory;
use App\Services\Gain\GainCalculator;
use App\Http\Requests\Api\Admin\Gain\GainStoreRequest;
use App\Http\Requests\Api\Admin\Gain\GainUserHistoryIndexRequest;
use App\Http\Resources\PaginationCollection;
use DB;
use Auth;

class GainUserHistoryController extends Controller
{

    public function index(GainUserHistoryIndexRequest $request) {
        $data = $request->validated();
        $historys =  GainUserHistory::search($data)->orderBy('id', 'desc')->with('user')->paginate($request->input('page_size', 10));
        return new PaginationCollection($historys);
    }

    public function store(GainStoreRequest $request) {
        $data = $request->validated();
        $auth = Auth::user();
        $remark = $auth->id.'- 官方赠送';

        DB::beginTransaction();
        try {
            foreach($data['ids'] as $id){
                $user = User::findOrFail($id);
                $gainCalculator = new GainCalculator($user);
                $res = $gainCalculator->productCalculate([
                    'action' => 'admin_gift',
                    'param' => 'num,uid',
                    'remark' => $remark,
                    'num' => $data['num'],
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(1, '赠送暖心失败:'.$e->getMessage());
        }
        return $this->success(['id'=>$data['ids']],'赠送暖心成功');
    }

    public function consume(GainStoreRequest $request) {
        $data = $request->validated();
        $auth = Auth::user();
        $remark = $auth->id.'- 官方扣除';
        $userGains = GainUser::whereIn('user_id', $data['ids'])->where('slug', 'mark')->get();
        $flag = false;

        foreach($userGains as $userGain) {
            $mark = $userGain->number;
            if($mark - $data['num'] < 0) {
                $flag = true;
            }
        }

        if(count($data['ids']) !== count($userGains)) {
            return $this->error(1, "用户列表中存在用户的暖心不够扣减，请检查用户暖心情况。");
        }

        if($flag) {
            return $this->error(1, "用户列表中存在用户的暖心不够扣减，请检查用户暖心情况。");
        }

        $users = User::whereIn('id', $data['ids'])->get();
        DB::beginTransaction();
        try {
            foreach($users as $user){
                $gainCalculator = new GainCalculator($user);
                $gainCalculator->consumeCalculate([
                    'action' => 'admin_consume',
                    'param' => 'num,uid',
                    'remark' => $remark,
                    'num' => $data['num'],
                    'uid' => $user->id
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(1, '扣除暖心失败:'.$e->getMessage());
        }
        return $this->success(['id'=>$data['ids']],'扣除暖心成功');
    }
}
