<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gain\Gain;
use App\Models\Gain\GainRule;

class GainRuleMarkCommonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $marks = $this->getDatas();
        foreach ($marks as $mark) {
            $gain = Gain::firstWhere('slug', $mark['slug']);
            $mark['gain_id'] = $gain->id;
            GainRule::updateOrCreate([
                'slug' => $mark['slug'],
                'action' => $mark['action'],
            ], $mark);
        }
    }

    protected function getDatas()
    {
        $now = \Carbon\Carbon::now();
        return [
            [
                'slug'       => 'mark',
                'name'       => '新用户注册赠送',
                'action'     => 'register_finish',
                'param'      => 'num,uid',
                'rang'       => 'once',
                'rate'       => 1,
                'rule'       => 'num*1',
                'status'     => 1,
                'sort'       => null,
                'max_total'  => -1,
                'created_at' => $now,
                'updated_at' => $now,
            ], [
                'slug'       => 'mark',
                'name'       => '官方赠送',
                'action'     => 'admin_gift',
                'param'      => 'num,uid',
                'rang'       => 'daily',
                'rate'       => 5,
                'rule'       => 'num*1',
                'status'     => 1,
                'sort'       => null,
                'max_total' => 50000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'mark',
                'name'       => '每日签到',
                'action'     => 'check_in',
                'param'      => 'num,uid',
                'rang'       => 'daily',
                'rate'       => 1,
                'rule'       => 'num*1',
                'status'     => 1,
                'sort'       => null,
                'max_total'  => -1,
                'created_at' => $now,
                'updated_at' => $now,
            ], [
                'slug'       => 'mark',
                'name'       => '官方扣除',
                'action'     => 'admin_consume',
                'param'      => 'num,uid',
                'rang'       => 'daily',
                'rate'       => -1,
                'rule'       => 'num*(-1)',
                'status'     => 1,
                'sort'       => null,
                'max_total' => -1,
                'created_at' => $now,
                'updated_at' => $now,
            ], [
                'slug'       => 'mark',
                'name'       => '邀请用户赠送',
                'action'     => 'invite_register',
                'param'      => 'num,uid',
                'rang'       => 'daily',
                'rate'       => -1,
                'rule'       => 'num*1',
                'status'     => 1,
                'sort'       => null,
                'max_total' => -1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }
}
