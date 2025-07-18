<?php

namespace App\Http\Controllers\Api\Web\Org;

use App\Http\Controllers\Controller;
use App\Models\Org\OrgDepartmentUser;
use App\Models\Org\Organization;
use App\Models\User;
use App\Http\Requests\Api\Web\Org\OrgDepartmentUserIndexRequest;
use App\Http\Requests\Api\Web\Org\OrgDepartmentUserStoreRequest;
use App\Http\Requests\Api\Web\Org\OrgDepartmentUserUpdateRequest;
use Illuminate\Support\Facades\DB;
use Auth;

class OrgDepartmentUserController extends Controller
{
    public function index($orgId, OrgDepartmentUserIndexRequest $request)
    {
        $exists = OrgDepartmentUser::where(['org_id' => $orgId, 'user_id' => Auth::id()])->exists();
        if(!$exists) {
            return $this->error(1, "非组织内部人员无法查看");
        }

        $data = $request->validated();
        $members = OrgDepartmentUser::with('user')->where('org_id', $orgId)->search($data)->get();
        return $this->success($members);
    }

    public function store($orgId, OrgDepartmentUserStoreRequest $request)
    {
        $org = Organization::find($orgId);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限");
        }

        $data = $request->validated();
        $data['org_id'] = $orgId;
        
        $user = User::firstWhere(['phone' => $data['phone']]);
        if($user) {
            $data['user_id'] = $user->id;
        }

        $exists = OrgDepartmentUser::where('org_id', $orgId)->where('phone', $data['phone'])->exists();
        if($exists) {
            return $this->error(1, "该手机号用户已在组织内");
        }

        $member = OrgDepartmentUser::create($data);
        $org->increment('member_count');

        return $this->success($member, '成员添加成功');
    }

    public function update($orgId, $id, OrgDepartmentUserUpdateRequest $request)
    {
        $org = Organization::find($orgId);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限");
        }

        $data = $request->validated();
        $member = OrgDepartmentUser::findOrFail($id);
        if(isset($data['phone']) && $member->user_id) {
            return $this->error(1, "用户已注册不可修改手机，请让用户重新绑定手机号，或添加新用户");
        }
        $member->update($data);
        return $this->success($member, '成员信息更新成功');
    }

    public function destroy($orgId, $id)
    {
        $org = Organization::find($orgId);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限");
        }
        
        $member = OrgDepartmentUser::findOrFail($id);
        DB::transaction(function () use ($member, $org) {
            $member->delete();
            $org->decrement('member_count');
        });
        return $this->success([], '成员移除成功');
    }

}