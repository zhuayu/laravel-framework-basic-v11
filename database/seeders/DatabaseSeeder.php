<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment() !== 'local') {
            die('只允许在本地环境运行');
        }
        $this->call(PermissionsSeeder::class);
        $this->call(AdministratorsSeeder::class);
        $this->call(VipSeeder::class);
        $this->call(GainSeeder::class);
        // $this->call(WechatAppSeeder::class);
    }
}
