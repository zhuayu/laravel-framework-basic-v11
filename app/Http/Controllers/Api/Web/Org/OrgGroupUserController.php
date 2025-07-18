<?php

namespace App\Http\Controllers\Api\Web\Org;

use App\Http\Controllers\Controller;
use App\Models\Org\OrgGroup;
use App\Models\Org\OrgGroupUser;
use App\Models\Org\OrgDepartmentUser;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Api\Web\Org\OrgGroupUserStoreRequest;
use App\Http\Requests\Api\Web\Org\OrgGroupUserUpdateRequest;
use DB;

class OrgGroupUserController extends Controller
{
    public function index($orgId, $groupId) {
        $exists = OrgGroupUser::where(['group_id' => $groupId, 'user_id' => Auth::id()])->exists();
        if(!$exists) {
            return $this->error(1, "非群成员无权限");
        }

        $groupUsers = OrgGroupUser::where(['group_id' => $groupId])->with('user')->get();
        return $this->success($groupUsers);
    }


    public function store(OrgGroupUserStoreRequest $request, $orgId, $groupId)
    {
        $data = $request->validated();
        $userId = $data['user_id'];

        $exists = OrgGroupUser::where(['group_id' => $groupId, 'user_id' => Auth::id()])
            ->whereIn('role', [OrgGroupUser::ROLE_OWNER, OrgGroupUser::ROLE_ADMIN ])
            ->exists();
        if(!$exists) {
            return $this->error(1, "非组织群拥有者或管理员无权限操作");
        }

        $group = OrgGroup::where('org_id', $orgId)->findOrFail($groupId);
        $userExists = OrgGroupUser::where('group_id', $groupId)->where('user_id', $userId)->exists();
        if ($userExists) {
            return $this->error(1, "用户已经在群中");
        }

        $userInOrgExists = OrgDepartmentUser::where(['org_id' => $orgId, 'user_id' => $data['user_id']])->exists();
        if(!$userInOrgExists) {
            return $this->error(1, "用户尚未加入组织");
        }

        // 添加成员到群组
        $member = OrgGroupUser::create([
            'group_id' => $groupId,
            'org_id' => $orgId,
            'user_id' => $userId,
            'creator_id' => Auth::id(),
            'role' => $data['role'],
        ]);
        $group->increment('member_count');
        return $this->success($member, '成员添加成功');
    }

    public function update(OrgGroupUserUpdateRequest $request, $orgId, $groupId, $id)
    {
        $data = $request->validated();
        $exists = OrgGroupUser::where(['group_id' => $groupId, 'user_id' => Auth::id()])
            ->whereIn('role', [OrgGroupUser::ROLE_OWNER, OrgGroupUser::ROLE_ADMIN ])
            ->exists();
        if(!$exists) {
            return $this->error(1, "非组织群拥有者或管理员无权限操作");
        }

        $member = OrgGroupUser::where('group_id', $groupId)->findOrFail($id);
        $member->update($data);
        return $this->success($member, '成员角色更新成功');
    }

    // 从群组移除成员
    public function destroy($orgId, $groupId, $id)
    {
        $exists = OrgGroupUser::where(['group_id' => $groupId, 'user_id' => Auth::id()])
            ->whereIn('role', [OrgGroupUser::ROLE_OWNER, OrgGroupUser::ROLE_ADMIN ])
            ->exists();
        if(!$exists) {
            return $this->error(1, "非组织群拥有者或管理员无权限操作");
        }

        $member = OrgGroupUser::where('org_id', $orgId)->findOrFail($id);
        DB::transaction(function () use ($member, $id) {
            $member->delete();
            OrgGroup::where('id', $id)->decrement('member_count');
        });

        return $this->success([], '成员移除成功');
    }
}