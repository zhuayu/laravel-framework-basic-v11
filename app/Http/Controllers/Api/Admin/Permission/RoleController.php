<?php

namespace App\Http\Controllers\Api\Admin\Permission;

use App\Http\Controllers\Controller;
use App\Models\Permission\Role;
use App\Http\Resources\PaginationCollection;
use App\Http\Requests\Api\Admin\Permission\RoleRequest;
use DB;

class RoleController extends Controller
{
    public function index() {
        $roles = Role::with('permissions')->orderBy('id', 'DESC')->get();
        return $this->success($roles);
    }

    public function store(RoleRequest $request) {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $role = Role::create($data);
            $role->syncPermissions($data['permission_ids']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('角色新增异常：' . $e->getMessage());
            return $this->error('角色新增异常');
        }
        return $this->success(['role_id' => $role->id], '角色新增成功');
    }

    public function show($id) {
        $role = Role::findOrFail($id);
        $data = [
            'id' => $role->id,
            'name' => $role->name,
            'display_name' => $role->display_name,
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('id'),
        ];
        return $this->success($data);
    }

    public function update(RoleRequest $request, $id) {
        $data = $request->validated();
        $role = Role::findOrFail($id);
        DB::beginTransaction();
        try {
            $role->update($data);
            // 更新权限
            $role->syncPermissions($data['permission_ids']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('角色编辑异常：' . $e->getMessage());
            return $this->error('角色编辑异常');
        }
        return $this->success(['role_id' => $role->id], '角色编辑成功');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $flag = $role->administrators()->exists();
        if ($flag) {
            return $this->error(1, '角色使用中，请移除管理员中该角色再进行删除操作');
        }
        $role->delete();
        return $this->success(null, '角色删除成功');
    }
}
