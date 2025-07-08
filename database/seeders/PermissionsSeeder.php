<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Permission\Permission;
use App\Models\Permission\PermissionTab;
use App\Models\Permission\PermissionGroup;
use App\Models\Permission\Role;
use App\Models\Permission\PermissionRole;

class PermissionsSeeder extends Seeder
{
    public $tabs = [
        [
            'name' => 'admin',
            'display_name' => '管理中心',
            'groups' => [
                [
                    'name' => 'auth-manage',
                    'display_name' => '权限管理',
                    'permissions' => [
                        ['name' => 'permissions-index', 'display_name' => '权限-所有权限'],
                        ['name' => 'roles-index', 'display_name' => '角色-列表'],
                        ['name' => 'roles-show', 'display_name' => '角色-详情'],
                        ['name' => 'roles-store', 'display_name' => '角色-添加'],
                        ['name' => 'roles-update', 'display_name' => '角色-编辑'],
                        ['name' => 'roles-delete', 'display_name' => '角色-删除'],
                        ['name' => 'administrators-index', 'display_name' => '管理员-列表'],
                        ['name' => 'administrators-store', 'display_name' => '管理员-添加'],
                        ['name' => 'administrators-update', 'display_name' => '管理员-编辑'],
                        ['name' => 'administrators-delete', 'display_name' => '管理员-删除']
                    ]
                ],
            ]
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $role = Role::updateOrCreate(['name' => 'admin'], [
            'display_name' => '管理员',
        ]);

        foreach ($this->tabs as $tabIndex => $tabInfo) {
            $tab = PermissionTab::updateOrCreate([
                'name' => $tabInfo['name'],
            ], [
                'display_name' => $tabInfo['display_name'],
                'sort' => $tabIndex + 1,
            ]);


            foreach ($tabInfo['groups'] as $groupIndex => $groupInfo) {
                $group = PermissionGroup::updateOrCreate([
                    'name' => $groupInfo['name'],
                ], [
                    'tab_id' => $tab->id,
                    'display_name' => $groupInfo['display_name'],
                    'sort' => $groupIndex + 1,
                ]);


                foreach ($groupInfo['permissions'] as $permissionKey => $permissionInfo) {
                    $permission = Permission::updateOrCreate([
                        'name' => $permissionInfo['name'],
                    ], [
                        'display_name' => $permissionInfo['display_name'],
                        'sort' => $permissionKey + 1,
                        'group_id' => $group->id,
                    ]);
                    PermissionRole::updateOrCreate([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id
                    ]);
                }
            }
        }
    }
}
