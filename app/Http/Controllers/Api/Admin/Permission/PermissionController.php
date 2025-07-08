<?php

namespace App\Http\Controllers\Api\Admin\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission\PermissionTab;
use Auth;

class PermissionController extends Controller
{
    public function index() {
        $tabs = PermissionTab::with([
            'permissionGroups.permissions',
        ])->where('name', 'admin')->first();
        return $this->success($tabs);
    }

    public function my() {
        $administrator = Auth::user();
        if(!$administrator){
            return $this->success([
                'permissions' => []
            ]);
        }
        $permissions = $administrator->allPermissions()
            ->pluck('name')
            ->toArray();
        return $this->success(compact('permissions'));
    }
}
