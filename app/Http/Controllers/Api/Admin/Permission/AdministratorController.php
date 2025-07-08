<?php

namespace App\Http\Controllers\Api\Admin\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission\Role;
use App\Models\Permission\Administrator;
use App\Models\Permission\RoleUser;
use App\Models\Permission\PermissionUser;
use App\Http\Resources\PaginationCollection;
use App\Http\Requests\Api\Admin\Permission\AdministratorRequest;
use DB;

class AdministratorController extends Controller
{
    // https://laratrust.santigarcor.me/docs/6.x/usage/querying-relationships.html#all-permissions
    public function index(Request $request)
    {
        $roles = $request->role_name
            ? [$request->role_name]
            : Role::get()->pluck('name')->toArray();

        $administrators = Administrator::with('roles')
            ->whereHas('roles', function ($query) use ($roles) {
                $query->whereIn('name', $roles);
            })
            ->orderByDesc('id')
            ->paginate($request->input('page_size', 10));
        return new PaginationCollection($administrators);
    }

    public function store(AdministratorRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $admin = Administrator::updateOrCreate(['phone' => $data['phone']], $data);
            $admin->syncRoles($request->role_ids);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('创建管理员异常' . $e->getMessage());
        }
        return $this->success(['id' => $admin->id]);
    }

    public function show($id)
    {
        $admin = Administrator::with(['roles'])->findOrFail($id);
        return $this->success($admin);
    }

    public function update(AdministratorRequest $request, $id)
    {
        $data = $request->validated();
        $admin = Administrator::findOrFail($id);
        DB::beginTransaction();
        try {
            $admin->syncRoles($request->role_ids);
            $admin->update($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('编辑管理员异常' . $e->getMessage());
        }
        return $this->success(null);
    }

    public function destroy($id)
    {
        RoleUser::where(['user_id' => $id])->delete();
        PermissionUser::where(['user_id' => $id])->delete();
        return $this->success(null, '移除管理员成功');
    }

}
