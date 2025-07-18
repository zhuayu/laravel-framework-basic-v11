<?php

namespace App\Http\Controllers\Api\Web\Org;

use App\Http\Controllers\Controller;
use App\Models\Org\Organization;
use App\Models\Org\OrgGroup;
use App\Models\Org\OrgGroupUser;
use App\Http\Requests\Api\Web\Org\OrgGroupIndexRequest;
use App\Http\Requests\Api\Web\Org\OrgGroupStoreRequest;
use App\Http\Requests\Api\Web\Org\OrgGroupUpdateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrgGroupController extends Controller
{
    public function index(OrgGroupIndexRequest $request, $orgId)
    {
        $org = Organization::find($orgId);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限");
        }
        $data = $request->validated();
        $groups = OrgGroup::where('org_id', $orgId)->search($data)->get();
        return $this->success($groups);
    }

    public function store(OrgGroupStoreRequest $request, $orgId)
    {
        $data = $request->validated();
        $org = Organization::find($orgId);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限");
        }

        $user = Auth::user();
        $data['creator_id'] = Auth::id();
        $data['org_id'] = $orgId;
        DB::transaction(function () use ($data, $user, &$group) {
            $group = OrgGroup::create($data);
            OrgGroupUser::create([
                'org_id' => $data['org_id'],
                'group_id' => $group->id,
                'user_id' => $user->id,
                'creator_id' => $user->id,
                'role' => OrgGroupUser::ROLE_OWNER,
            ]);
            $group->increment('member_count');
        });
        return $this->success($group, '群组创建成功');
    }

    public function show($orgId, $id)
    {
        $exists = OrgGroupUser::where(['group_id' => $id, 'user_id' => Auth::id()])->exists();
        if(!$exists) {
            return $this->error(1, "非群成员无权限");
        }
        $group = OrgGroup::with(['creator', 'members.user'])
            ->where('org_id', $orgId)->findOrFail($id);
        return $this->success($group);
    }

    public function update(OrgGroupUpdateRequest $request, $orgId, $id)
    {
        $data = $request->validated();
        $exists = OrgGroupUser::where(['group_id' => $id, 'user_id' => Auth::id()])
            ->whereIn('role', [OrgGroupUser::ROLE_OWNER, OrgGroupUser::ROLE_ADMIN ])->exists();
        if(!$exists) {
            return $this->error(1, "非组织群拥有者或管理员无权限操作");
        }

        $group = OrgGroup::where('org_id', $orgId)->findOrFail($id);
        $group->update($data);
        return $this->success($group, '群组更新成功');
    }

    public function destroy($orgId, $id)
    {
        $exists = OrgGroupUser::where(['group_id' => $id, 'user_id' => Auth::id()])
            ->whereIn('role', [OrgGroupUser::ROLE_OWNER, OrgGroupUser::ROLE_ADMIN ])->exists();
        if(!$exists) {
            return $this->error(1, "非组织群拥有者或管理员无权限操作");
        }

        $group = OrgGroup::where('org_id', $orgId)->findOrFail($id);
        DB::transaction(function () use ($group) {
            $group->members()->delete();
            $group->delete();
        });

        return $this->success([], '群组删除成功');
    }
}