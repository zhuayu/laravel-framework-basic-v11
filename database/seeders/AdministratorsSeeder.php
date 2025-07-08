<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission\Role;
use App\Models\Permission\Administrator;

class AdministratorsSeeder extends Seeder
{
    private $administrators = [
        [
            'name'=>'Jax',
            'phone' => 13500000000
        ],
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->administrators as $key => $administrator){
            $administrator = Administrator::updateOrCreate($administrator);
            $role = Role::updateOrCreate([ 'name' => 'admin']);
            $administrator->syncRoles([$role]);
        }
    }
}
