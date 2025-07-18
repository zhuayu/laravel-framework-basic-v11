<?php

namespace App\Http\Controllers\Api\Web\Org;

use App\Http\Controllers\Controller;
use App\Models\Org\Organization;
use App\Models\Org\OrgDepartment;
use App\Http\Requests\Api\Web\Org\OrgDepartmentStoreRequest;
use App\Http\Requests\Api\Web\Org\OrgDepartmentUpdateRequest;
use App\Http\Requests\Api\Web\Org\OrgDepartmentMoveRequest;
use Auth;

class OrgDepartmentController extends Controller
{
    public function index($orgId)
    {
        $org = Organization::find($orgId);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限");
        }

        $tree = OrgDepartment::getTree($orgId);
        return $this->success($tree);
    }

    public function store(OrgDepartmentStoreRequest $request, $orgId)
    {
        $org = Organization::find($orgId);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限");
        }

        $data = $request->validated();
        $parent = OrgDepartment::where('org_id', $orgId)->findOrFail($request->parent_id);
        if(!$parent) {
            return $this->error(1, "parent_id 有误");
        }

        $department = $parent->createChild([
            'name' => $request->name,
            'org_id' => $orgId
        ]);

        return $this->success($department);
    }

    public function update(OrgDepartmentUpdateRequest $request, $orgId, $id)
    {
        $org = Organization::find($orgId);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限");
        }

        $data = $request->validated();
        $department = OrgDepartment::where('org_id', $orgId)->findOrFail($id);
        $department->update($data);
        return $this->success($department);
    }

    public function destroy($orgId, $id)
    {
        $org = Organization::find($orgId);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限");
        }

        $department = OrgDepartment::where('org_id', $orgId)->findOrFail($id);
        if ($department->children()->count() > 0) {
            return $this->error(1, "请先删除子部门");
        }
        
        if ($department->members()->count() > 0) {
            return $this->error(1, "请先移除部门成员");
        }
        
        $department->delete();
        return $this->success([], "部门移除成功");
    }
    
    public function move(OrgDepartmentMoveRequest $request, $orgId, $id)
    {
        $org = Organization::find($orgId);
        if($org->creator_id !== Auth::id()) {
            return $this->error(1, "非组织创建者无权限");
        }

        $data = $request->validated();
        $department = OrgDepartment::where('org_id', $orgId)->findOrFail($id);
        $target = OrgDepartment::where('org_id', $orgId)->findOrFail($request->target_id);
            
        switch ($request->position) {
            case 'before':
                $department->insertBeforeNode($target);
                break;
                
            case 'after':
                $department->insertAfterNode($target);
                break;
                
            case 'inside':
                $target->prependNode($department);
                break;
        }

        return $this->success($department, "部门位置移动成功");
    }
}