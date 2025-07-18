<?php

namespace App\Http\Controllers\Api\Web\Org;

use App\Http\Controllers\Controller;
use App\Models\Org\Organization;
use App\Models\Org\OrgDepartment;
use App\Models\Org\OrgDepartmentUser;
use App\Http\Requests\Api\Web\Org\OrganzationStoreRequest;
use App\Http\Requests\Api\Web\Org\OrganzationUpdateRequest;
use App\Http\Requests\Api\Web\Org\OrganzationIndexRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{

    public function index(OrganzationIndexRequest $request)
    {
        $data = $request->validated();
        $orgs = Organization::search($data)->get();
        return $this->success($orgs);
    }

    public function store(OrganzationStoreRequest $request)
    {
        $data = $request->validated();
        $exists = Organization::where(['name' => $request->name])->exists();
        if($exists) {
            return $this->error(1, "组织名已被注册");
        }

        $user = Auth::user();
        $data['creator_id'] = $user->id;

        DB::transaction(function () use ($data, $user, &$org) {
            // 创建组织
            $org = Organization::create($data);
            // 为组织创建根部门"全部"
            $rootDepartment = OrgDepartment::create(['org_id' => $org->id,'name' => '全部']);
            $rootDepartment->makeRoot()->save();
            // 创建企业管理员
            OrgDepartmentUser::create([
                'org_id' => $org->id, 
                'dept_id' => $rootDepartment->id, 
                'user_id' => $user->id, 
                'phone' => $user->phone, 
                'name'  => $user->name, 
                'status' => 1
            ]);
            $org->increment('member_count');
        });

        return $this->success(['id' => $org->id], "组织申请已提交，等待审");
    }

    public function show($id) {
        $org = Organization::find($id);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限");
        }
        return $this->success($org);
    }

    public function update(OrganzationUpdateRequest $request, $id) {

        $data = $request->validated();
        $org = Organization::find($id);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限操作");
        }

        if(isset($data['name'])) {
            $exists = Organization::where(['name' => $request->name])
                ->where('id', '!=', $org->id)
                ->exists();
            if($exists) {
                return $this->error(1, "组织名已被注册");
            }
        }

        $org->update($data);
        return $this->success($org);
    }

    public function destroy($id) {
        $org = Organization::find($id);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限操作");
        }
        $org->delete();
        return $this->success([]);
    }
}